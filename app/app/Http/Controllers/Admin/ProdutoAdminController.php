<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Produto;
use App\Models\Categoria;
use App\Models\ProdutoImagem;
use App\Http\Requests\ProdutoRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class ProdutoAdminController extends Controller
{
    /**
     * Listagem admin
     */
    public function index(Request $request)
    {
        $query = Produto::query()->with(['categoria']);
        
        // Busca
        if ($request->has('busca') && $request->busca) {
            $query->buscar($request->busca);
        }
        
        // Filtro por status
        if ($request->has('status') && $request->status) {
            switch ($request->status) {
                case 'disponivel':
                    $query->disponivel();
                    break;
                case 'indisponivel':
                    $query->where('disponibilidade', Produto::INDISPONIVEL);
                    break;
                case 'estoque_baixo':
                    $query->baixoEstoque();
                    break;
                case 'inativo':
                    $query->where('ativo', false);
                    break;
            }
        }
        
        // Filtro por categoria
        if ($request->has('categoria') && $request->categoria) {
            $query->where('categoria_id', $request->categoria);
        }
        
        $produtos = $query->latest()->paginate(15);
        $categorias = Categoria::ativo()->ordenado()->get();
        
        return view('admin.produtos.index', compact('produtos', 'categorias'));
    }

    /**
     * Formulário de criação
     */
    public function create()
    {
        $categorias = Categoria::ativo()->ordenado()->get();
        return view('admin.produtos.create', compact('categorias'));
    }

    /**
     * Salvar produto
     */
    public function store(ProdutoRequest $request)
    {
        $data = $request->validated();
        
        // Gerar slug
        $data['slug'] = Str::slug($data['descricao']);
        
        // Processar imagem principal
        if ($request->hasFile('imagem')) {
            $data['imagem'] = $this->uploadImagem($request->file('imagem'));
        }
        
        // Calcular disponibilidade
        $data['disponibilidade'] = $this->calcularDisponibilidade(
            $data['quantidade'] ?? 0,
            $data['ativo'] ?? true
        );
        
        $produto = Produto::create($data);
        
        // Processar imagens adicionais
        if ($request->hasFile('imagens')) {
            foreach ($request->file('imagens') as $index => $imagem) {
                $nome = $this->uploadImagem($imagem);
                ProdutoImagem::create([
                    'produto_id' => $produto->id,
                    'imagem' => $nome,
                    'ordem' => $index,
                    'principal' => $index === 0,
                ]);
            }
        }
        
        return redirect()
            ->route('admin.produtos.index')
            ->with('success', 'Produto criado com sucesso!');
    }

    /**
     * Mostrar produto
     */
    public function show($id)
    {
        $produto = Produto::with(['categoria', 'imagens', 'itensPedido'])
            ->findOrFail($id);
        return view('admin.produtos.show', compact('produto'));
    }

    /**
     * Formulário de edição
     */
    public function edit($id)
    {
        $produto = Produto::with(['imagens'])->findOrFail($id);
        $categorias = Categoria::ativo()->ordenado()->get();
        return view('admin.produtos.edit', compact('produto', 'categorias'));
    }

    /**
     * Atualizar produto
     */
    public function update(ProdutoRequest $request, $id)
    {
        $produto = Produto::findOrFail($id);
        $data = $request->validated();
        
        // Gerar slug se mudou
        if ($produto->descricao !== $data['descricao']) {
            $data['slug'] = Str::slug($data['descricao']);
        }
        
        // Processar nova imagem principal
        if ($request->hasFile('imagem')) {
            // Remover imagem antiga
            if ($produto->imagem) {
                Storage::disk('public')->delete('produtos/' . $produto->imagem);
            }
            $data['imagem'] = $this->uploadImagem($request->file('imagem'));
        }
        
        // Processar imagens adicionais
        if ($request->hasFile('imagens')) {
            foreach ($request->file('imagens') as $index => $imagem) {
                $nome = $this->uploadImagem($imagem);
                ProdutoImagem::create([
                    'produto_id' => $produto->id,
                    'imagem' => $nome,
                    'ordem' => $index,
                ]);
            }
        }
        
        // Atualizar disponibilidade
        $data['disponibilidade'] = $this->calcularDisponibilidade(
            $data['quantidade'] ?? $produto->quantidade,
            $data['ativo'] ?? $produto->ativo
        );
        
        $produto->update($data);
        
        return redirect()
            ->route('admin.produtos.index')
            ->with('success', 'Produto atualizado com sucesso!');
    }

    /**
     * Excluir produto
     */
    public function destroy($id)
    {
        $produto = Produto::findOrFail($id);
        
        // Verificar se tem pedidos
        if ($produto->itensPedido()->exists()) {
            return back()->with('error', 'Não é possível excluir um produto que possui pedidos.');
        }
        
        // Remover imagens
        if ($produto->imagem) {
            Storage::disk('public')->delete('produtos/' . $produto->imagem);
        }
        
        foreach ($produto->imagens as $imagem) {
            Storage::disk('public')->delete('produtos/' . $imagem->imagem);
            $imagem->delete();
        }
        
        $produto->delete();
        
        return redirect()
            ->route('admin.produtos.index')
            ->with('success', 'Produto excluído com sucesso!');
    }

    /**
     * Ajustar estoque
     */
    public function ajustarEstoque(Request $request, $id)
    {
        $request->validate([
            'quantidade' => 'required|integer|min:1',
            'operacao' => 'required|in:adicionar,remover,definir',
        ]);
        
        $produto = Produto::findOrFail($id);
        
        switch ($request->operacao) {
            case 'adicionar':
                $produto->aumentarEstoque($request->quantidade);
                $mensagem = "Adicionados {$request->quantidade} itens ao estoque.";
                break;
            case 'remover':
                if (!$produto->reduzirEstoque($request->quantidade)) {
                    return back()->with('error', 'Estoque insuficiente!');
                }
                $mensagem = "Removidos {$request->quantidade} itens do estoque.";
                break;
            case 'definir':
                $produto->quantidade = $request->quantidade;
                $produto->atualizarDisponibilidade();
                $produto->save();
                $mensagem = "Estoque definido para {$request->quantidade} itens.";
                break;
        }
        
        return back()->with('success', $mensagem);
    }

    /**
     * Exportar produtos (CSV)
     */
    public function export(Request $request)
    {
        $produtos = Produto::with('categoria')->get();
        
        $filename = 'produtos_' . date('Y-m-d') . '.csv';
        $handle = fopen('php://output', 'w');
        
        // UTF-8 BOM
        fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));
        
        // Cabeçalhos
        fputcsv($handle, [
            'ID', 'Descrição', 'Categoria', 'Preço Unitário', 
            'Preço Atacado', 'Preço Promocional', 'Quantidade', 
            'Disponibilidade', 'Ativo', 'Referência', 'Visualizações',
            'Destaque', 'Novo', 'Mais Vendido', 'IPI', 'Data Criação'
        ], ';');
        
        // Dados
        foreach ($produtos as $produto) {
            fputcsv($handle, [
                $produto->id,
                $produto->descricao,
                $produto->categoria->nome ?? '',
                number_format($produto->valor_unitario, 2, ',', '.'),
                number_format($produto->valor_atacado ?? 0, 2, ',', '.'),
                number_format($produto->preco_promocional ?? 0, 2, ',', '.'),
                $produto->quantidade,
                $produto->status_label,
                $produto->ativo ? 'Sim' : 'Não',
                $produto->referencia ?? '',
                $produto->visualizacoes,
                $produto->destaque ? 'Sim' : 'Não',
                $produto->novo ? 'Sim' : 'Não',
                $produto->mais_vendido ? 'Sim' : 'Não',
                number_format($produto->ipi ?? 0, 2, ',', '.'),
                $produto->created_at->format('d/m/Y H:i'),
            ], ';');
        }
        
        fclose($handle);
        
        return response()->stream(
            function() {},
            200,
            [
                'Content-Type' => 'text/csv; charset=UTF-8',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ]
        );
    }

    /**
     * Upload de imagem
     */
    private function uploadImagem($file): string
    {
        $filename = time() . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();
        $file->storeAs('produtos', $filename, 'public');
        return $filename;
    }

    /**
     * Calcular disponibilidade
     */
    private function calcularDisponibilidade(int $quantidade, bool $ativo): string
    {
        if (!$ativo) {
            return Produto::INDISPONIVEL;
        }
        
        if ($quantidade <= 0) {
            return Produto::INDISPONIVEL;
        }
        
        if ($quantidade <= 5) {
            return Produto::ESTOQUE_BAIXO;
        }
        
        return Produto::DISPONIVEL;
    }

    /**
     * Remover imagem
     */
    public function removerImagem($id)
    {
        $imagem = ProdutoImagem::findOrFail($id);
        
        Storage::disk('public')->delete('produtos/' . $imagem->imagem);
        $imagem->delete();
        
        return response()->json(['success' => true]);
    }

    /**
     * Definir imagem principal
     */
    public function definirPrincipal($id)
    {
        $imagem = ProdutoImagem::findOrFail($id);
        
        // Remover principal das outras imagens
        ProdutoImagem::where('produto_id', $imagem->produto_id)
            ->update(['principal' => false]);
        
        $imagem->principal = true;
        $imagem->save();
        
        return response()->json(['success' => true]);
    }
}