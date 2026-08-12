<?php

namespace App\Http\Controllers;

use App\Models\Produto;
use Illuminate\Http\Request;

class ProdutoController extends Controller
{
    public function index(Request $request)
    {
        $query = Produto::query()
            ->where('ativo', true)
            ->where('quantidade', '>', 0)
            ->where('disponibilidade', 'DISPONÍVEL');

        // Filtro por categoria
        if ($request->has('categoria') && $request->categoria) {
            $query->where('categoria', 'LIKE', '%' . $request->categoria . '%');
        }

        // Filtro por faixa de preço
        if ($request->has('preco_min') && $request->preco_min) {
            $query->where(function($q) use ($request) {
                $q->where('valor_unitario', '>=', $request->preco_min)
                  ->orWhere('preco_promocional', '>=', $request->preco_min);
                  // Removido 'valor_atacado' se não existir
            });
        }

        if ($request->has('preco_max') && $request->preco_max) {
            $query->where(function($q) use ($request) {
                $q->where('valor_unitario', '<=', $request->preco_max)
                  ->orWhere('preco_promocional', '<=', $request->preco_max);
            });
        }

        // Ordenação
        $orderBy = $request->get('order', 'created_at');
        $allowedOrders = ['created_at', 'valor_unitario', 'descricao', 'categoria'];
        if (!in_array($orderBy, $allowedOrders)) {
            $orderBy = 'created_at';
        }

        $orderDir = $request->get('dir', 'desc');
        $allowedDirs = ['asc', 'desc'];
        if (!in_array($orderDir, $allowedDirs)) {
            $orderDir = 'desc';
        }

        $query->orderBy($orderBy, $orderDir);

        $produtos = $query->paginate(24);
        $produtos->appends($request->all());

        return view('produtos.index', compact('produtos'));
    }

    public function show($slug)
    {
        $produto = Produto::where('slug', $slug)
            ->where('ativo', true)
            ->firstOrFail();

        // Produtos relacionados da mesma categoria
        $relacionados = Produto::where('categoria', $produto->categoria)
            ->where('id', '!=', $produto->id)
            ->where('ativo', true)
            ->where('disponibilidade', 'DISPONÍVEL')
            ->where('quantidade', '>', 0)
            ->take(6)
            ->get();

        // Incrementa contador de visualizações (se a coluna existir)
        if (Schema::hasColumn('produtos', 'visualizacoes')) {
            $produto->increment('visualizacoes');
        }

        return view('produtos.show', compact('produto', 'relacionados'));
    }

    public function porCategoria($categoria)
    {
        $produtos = Produto::where('categoria', 'LIKE', '%' . $categoria . '%')
            ->where('ativo', true)
            ->where('disponibilidade', 'DISPONÍVEL')
            ->where('quantidade', '>', 0)
            ->orderBy('created_at', 'desc')
            ->paginate(24);

        return view('produtos.index', compact('produtos'));
    }

    /**
     * 🔥 BUSCA CORRIGIDA
     */
    public function buscar(Request $request)
    {
        $search = trim($request->get('q'));

        // Se não tiver termo de busca, redireciona
        if (empty($search)) {
            return redirect()->route('produtos.index')
                ->with('warning', 'Digite um termo para buscar.');
        }

        // 🔥 BUSCA CORRIGIDA - Usando LIKE (MySQL)
        $produtos = Produto::where('ativo', true)
            ->where('disponibilidade', 'DISPONÍVEL')
            ->where('quantidade', '>', 0)
            ->where(function ($query) use ($search) {
                $query->where('descricao', 'LIKE', '%' . $search . '%')
                    ->orWhere('categoria', 'LIKE', '%' . $search . '%');
                
                // Só busca por 'referencia' se a coluna existir
                if (Schema::hasColumn('produtos', 'referencia')) {
                    $query->orWhere('referencia', 'LIKE', '%' . $search . '%');
                }
            })
            ->orderBy('descricao', 'asc') // Ordenação simples
            ->paginate(24);

        // Mantém o termo de busca na view
        return view('produtos.index', compact('produtos', 'search'));
    }

    /**
     * 🔥 MÉTODO CORRIGIDO - Produtos em destaque
     */
    public function destaques()
    {
        // Se o método emDestaque() não existir no Model, use assim:
        $produtos = Produto::where('ativo', true)
            ->where('disponibilidade', 'DISPONÍVEL')
            ->where('quantidade', '>', 0)
            ->orderBy('created_at', 'desc')
            ->take(8)
            ->get();

        return response()->json($produtos);
    }
}