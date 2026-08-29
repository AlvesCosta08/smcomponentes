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
    // Constantes de disponibilidade
    const DISPONIVEL = 'DISPONIVEL';
    const INDISPONIVEL = 'INDISPONIVEL';
    const ESTOQUE_BAIXO = 'ESTOQUE_BAIXO';

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
                    $query->where('disponibilidade', self::INDISPONIVEL);
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
        $margens = Produto::getMargensDisponiveis();
        return view('admin.produtos.create', compact('categorias', 'margens'));
    }

    public function store(ProdutoRequest $request)
    {
        $data = $request->validated();
        
        // Gerar slug
        $data['slug'] = Str::slug($data['descricao'] . '-' . Str::random(6));
        
        // ✅ CORRIGIDO: Criar produto e depois calcular preços
        $produto = new Produto($data);
        
        // Calcular preços usando o método do model
        $produto->calcularTodosPrecos();
        
        // Processar imagem principal
        if ($request->hasFile('imagem')) {
            $produto->imagem = $this->uploadImagem($request->file('imagem'));
        }
        
        // Calcular disponibilidade
        $produto->disponibilidade = $this->calcularDisponibilidade(
            $produto->quantidade ?? 0,
            $produto->ativo ?? true
        );
        
        // Salvar produto
        $produto->save();
        
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
        $margens = Produto::getMargensDisponiveis();
        return view('admin.produtos.edit', compact('produto', 'categorias', 'margens'));
    }

    public function update(ProdutoRequest $request, $id)
    {
        $produto = Produto::findOrFail($id);
        $data = $request->validated();
        
        // Gerar slug se mudou
        if ($produto->descricao !== $data['descricao']) {
            $data['slug'] = Str::slug($data['descricao'] . '-' . Str::random(6));
        }
        
        // ✅ CORRIGIDO: Atualizar dados primeiro
        $produto->fill($data);
        
        // Recalcular preços
        $produto->calcularTodosPrecos();
        
        // Processar nova imagem principal
        if ($request->hasFile('imagem')) {
            if ($produto->imagem) {
                Storage::disk('public')->delete($produto->imagem);
            }
            $produto->imagem = $this->uploadImagem($request->file('imagem'));
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
        $produto->disponibilidade = $this->calcularDisponibilidade(
            $produto->quantidade ?? 0,
            $produto->ativo ?? true
        );
        
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

    private function calcularDisponibilidade(int $quantidade, bool $ativo): string
    {
        if (!$ativo) {
            return self::INDISPONIVEL;
        }
        
        if ($quantidade <= 0) {
            return self::INDISPONIVEL;
        }
        
        if ($quantidade <= 5) {
            return self::ESTOQUE_BAIXO;
        }
        
        return self::DISPONIVEL;
    }
}