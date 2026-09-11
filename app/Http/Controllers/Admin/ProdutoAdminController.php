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
    // 🔥 Removemos as constantes de disponibilidade (agora usamos status)
    // Mantemos apenas para compatibilidade de filtros (opcional)
    const STATUS_DISPONIVEL = 'disponivel';
    const STATUS_INDISPONIVEL = 'indisponivel';
    const STATUS_SOB_ENCOMENDA = 'sob_encomenda';
    const STATUS_ESTOQUE_BAIXO = 'estoque_baixo'; // usado apenas para filtro

    public function index(Request $request)
    {
        $query = Produto::query()->with(['categoria']);

        if ($request->has('busca') && $request->busca) {
            $query->buscar($request->busca);
        }

        if ($request->has('status') && $request->status) {
            switch ($request->status) {
                case 'disponivel':
                    $query->disponivel();
                    break;
                case 'indisponivel':
                    $query->where('status', self::STATUS_INDISPONIVEL);
                    break;
                case 'estoque_baixo':
                    $query->baixoEstoque();
                    break;
                case 'inativo':
                    $query->where('ativo', false);
                    break;
            }
        }

        if ($request->has('categoria') && $request->categoria) {
            $query->where('categoria_id', $request->categoria);
        }

        $produtos = $query->latest()->paginate(15);
        $categorias = Categoria::ativo()->ordenado()->get();

        return view('admin.produtos.index', compact('produtos', 'categorias'));
    }

    public function create()
    {
        $categorias = Categoria::ativo()->ordenado()->get();
        // 🔥 Removido $margens (não mais usado)
        return view('admin.produtos.create', compact('categorias'));
    }

    public function store(ProdutoRequest $request)
    {
        $data = $request->validated();

        // Gerar slug (se não vier do request)
        if (empty($data['slug'])) {
            $data['slug'] = Str::slug($data['descricao'] . '-' . Str::random(6));
        }

        // 🔥 Criar produto sem cálculos automáticos
        $produto = new Produto($data);

        // Processar imagem principal
        if ($request->hasFile('imagem')) {
            $produto->imagem = $this->uploadImagem($request->file('imagem'));
        }

        // 🔥 Definir status baseado na quantidade e ativo (mas o Model já faz isso via boot)
        // Apenas garantimos que status tenha um valor padrão
        if (empty($produto->status)) {
            $produto->status = $this->calcularStatus($produto->quantidade ?? 0, $produto->ativo ?? true);
        }

        // Salvar produto (o boot do Model já gera slug e define status se necessário)
        $produto->save();

        // Processar imagens adicionais (galeria)
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

    public function show($id)
    {
        $produto = Produto::with(['categoria', 'imagens'])
            ->findOrFail($id);
        return view('admin.produtos.show', compact('produto'));
    }

    public function edit($id)
    {
        $produto = Produto::with(['imagens'])->findOrFail($id);
        $categorias = Categoria::ativo()->ordenado()->get();
        // 🔥 Removido $margens
        return view('admin.produtos.edit', compact('produto', 'categorias'));
    }

    public function update(ProdutoRequest $request, $id)
    {
        $produto = Produto::findOrFail($id);
        $data = $request->validated();

        // Gerar slug se descrição mudou
        if ($produto->descricao !== $data['descricao'] && empty($data['slug'])) {
            $data['slug'] = Str::slug($data['descricao'] . '-' . Str::random(6));
        }

        // 🔥 Atualizar dados (sem cálculos)
        $produto->fill($data);

        // Processar nova imagem principal
        if ($request->hasFile('imagem')) {
            if ($produto->imagem) {
                Storage::disk('public')->delete($produto->imagem);
            }
            $produto->imagem = $this->uploadImagem($request->file('imagem'));
        }

        // Processar imagens adicionais (apenas as novas)
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

        // 🔥 Atualizar status automaticamente (se quantidade ou ativo mudaram)
        // O Model já faz isso no boot, mas podemos forçar se necessário
        $produto->atualizarDisponibilidade();

        $produto->save();

        return redirect()
            ->route('admin.produtos.index')
            ->with('success', 'Produto atualizado com sucesso!');
    }

    public function destroy($id)
    {
        $produto = Produto::findOrFail($id);

        // Remover imagens
        if ($produto->imagem) {
            Storage::disk('public')->delete($produto->imagem);
        }

        foreach ($produto->imagens as $imagem) {
            Storage::disk('public')->delete($imagem->imagem);
            $imagem->delete();
        }

        $produto->delete();

        return redirect()
            ->route('admin.produtos.index')
            ->with('success', 'Produto excluído com sucesso!');
    }

    public function ajustarEstoque(Request $request, $id)
    {
        $request->validate([
            'quantidade' => 'required|integer|min:1',
            'operacao' => 'required|in:adicionar,remover,definir',
        ]);

        $produto = Produto::findOrFail($id);

        switch ($request->operacao) {
            case 'adicionar':
                $produto->quantidade += $request->quantidade;
                $mensagem = "Adicionados {$request->quantidade} itens ao estoque.";
                break;
            case 'remover':
                if ($produto->quantidade < $request->quantidade) {
                    return back()->with('error', 'Estoque insuficiente!');
                }
                $produto->quantidade -= $request->quantidade;
                $mensagem = "Removidos {$request->quantidade} itens do estoque.";
                break;
            case 'definir':
                $produto->quantidade = $request->quantidade;
                $mensagem = "Estoque definido para {$request->quantidade} itens.";
                break;
        }

        // 🔥 Atualizar status com base na nova quantidade
        $produto->atualizarDisponibilidade();
        $produto->ultima_atualizacao_estoque = now();
        $produto->save();

        return back()->with('success', $mensagem);
    }

    public function removerImagem($id)
    {
        $imagem = ProdutoImagem::findOrFail($id);
        Storage::disk('public')->delete($imagem->imagem);
        $imagem->delete();
        return response()->json(['success' => true]);
    }

    public function definirPrincipal($id)
    {
        $imagem = ProdutoImagem::findOrFail($id);
        ProdutoImagem::where('produto_id', $imagem->produto_id)->update(['principal' => false]);
        $imagem->principal = true;
        $imagem->save();
        return response()->json(['success' => true]);
    }

    // ==============================================
    // MÉTODOS PRIVADOS
    // ==============================================

    private function uploadImagem($file): string
    {
        $filename = time() . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();
        $path = $file->storeAs('produtos', $filename, 'public');
        return $path;
    }

    /**
     * 🔥 Novo método para calcular status (disponivel, indisponivel, sob_encomenda)
     * Pode ser usado como fallback caso o Model não defina automaticamente.
     */
    private function calcularStatus(int $quantidade, bool $ativo): string
    {
        if (!$ativo) {
            return self::STATUS_INDISPONIVEL;
        }

        if ($quantidade <= 0) {
            return self::STATUS_INDISPONIVEL;
        }

        // Se quiser, pode adicionar lógica para 'sob_encomenda' baseado em algo
        // Por enquanto, se tem estoque e está ativo, é 'disponivel'
        return self::STATUS_DISPONIVEL;
    }
}