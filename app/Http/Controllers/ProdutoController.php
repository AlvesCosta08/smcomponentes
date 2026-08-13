<?php

namespace App\Http\Controllers;

use App\Models\Produto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema; // ← IMPORTANTE!

class ProdutoController extends Controller
{
    public function index(Request $request)
    {
        $query = Produto::query()
            ->where('ativo', true)
            ->where('quantidade', '>', 0);

        // 🔥 ORDENAÇÃO PRIORITÁRIA: Disponíveis primeiro
        $query->orderByRaw("
            CASE 
                WHEN disponibilidade = 'DISPONIVEL' THEN 1
                WHEN disponibilidade = 'EST.BAIXO' THEN 2
                ELSE 3
            END
        ");

        // Filtro por categoria
        if ($request->has('categoria') && $request->categoria) {
            $query->where('categoria', 'LIKE', '%' . $request->categoria . '%');
        }

        // Filtro por faixa de preço
        if ($request->has('preco_min') && $request->preco_min) {
            $query->where(function($q) use ($request) {
                $q->where('valor_unitario', '>=', $request->preco_min)
                  ->orWhere('preco_promocional', '>=', $request->preco_min);
            });
        }

        if ($request->has('preco_max') && $request->preco_max) {
            $query->where(function($q) use ($request) {
                $q->where('valor_unitario', '<=', $request->preco_max)
                  ->orWhere('preco_promocional', '<=', $request->preco_max);
            });
        }

        // Ordenação secundária (aplicada depois da prioridade)
        $orderBy = $request->get('order', 'created_at');
        $allowedOrders = ['created_at', 'valor_unitario', 'descricao', 'categoria', 'quantidade'];
        if (!in_array($orderBy, $allowedOrders)) {
            $orderBy = 'created_at';
        }

        $orderDir = $request->get('dir', 'desc');
        $allowedDirs = ['asc', 'desc'];
        if (!in_array($orderDir, $allowedDirs)) {
            $orderDir = 'desc';
        }

        // Adiciona a ordenação secundária
        $query->orderBy($orderBy, $orderDir);

        $produtos = $query->paginate(24);
        $produtos->appends($request->all());

        // 🔥 Contagem para exibir na view
        $totais = [
            'disponiveis' => Produto::where('ativo', true)
                ->where('quantidade', '>', 0)
                ->where('disponibilidade', 'DISPONIVEL')
                ->count(),
            'estoque_baixo' => Produto::where('ativo', true)
                ->where('quantidade', '>', 0)
                ->where('disponibilidade', 'EST.BAIXO')
                ->count(),
            'indisponiveis' => Produto::where('ativo', true)
                ->where('quantidade', '>', 0)
                ->where('disponibilidade', 'INDISPONIVEL')
                ->count(),
            'total' => Produto::where('ativo', true)
                ->where('quantidade', '>', 0)
                ->count(),
        ];

        return view('produtos.index', compact('produtos', 'totais'));
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
            ->where('disponibilidade', 'DISPONIVEL')
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
            ->where('quantidade', '>', 0)
            ->orderByRaw("
                CASE 
                    WHEN disponibilidade = 'DISPONIVEL' THEN 1
                    WHEN disponibilidade = 'EST.BAIXO' THEN 2
                    ELSE 3
                END
            ")
            ->orderBy('created_at', 'desc')
            ->paginate(24);

        return view('produtos.index', compact('produtos'));
    }

    public function buscar(Request $request)
    {
        $search = trim($request->get('q'));

        if (empty($search)) {
            return redirect()->route('produtos.index')
                ->with('warning', 'Digite um termo para buscar.');
        }

        $produtos = Produto::where('ativo', true)
            ->where('quantidade', '>', 0)
            ->where(function ($query) use ($search) {
                $query->where('descricao', 'LIKE', '%' . $search . '%')
                    ->orWhere('categoria', 'LIKE', '%' . $search . '%');
                
                if (Schema::hasColumn('produtos', 'referencia')) {
                    $query->orWhere('referencia', 'LIKE', '%' . $search . '%');
                }
            })
            ->orderByRaw("
                CASE 
                    WHEN disponibilidade = 'DISPONIVEL' THEN 1
                    WHEN disponibilidade = 'EST.BAIXO' THEN 2
                    ELSE 3
                END
            ")
            ->orderBy('descricao', 'asc')
            ->paginate(24);

        return view('produtos.index', compact('produtos', 'search'));
    }

    public function destaques()
    {
        $produtos = Produto::where('ativo', true)
            ->where('disponibilidade', 'DISPONIVEL')
            ->where('quantidade', '>', 0)
            ->orderBy('created_at', 'desc')
            ->take(8)
            ->get();

        return response()->json($produtos);
    }

    /**
     * 🔥 FILTRO POR DISPONIBILIDADE
     * Exemplo: /produtos/filtro/disponiveis
     */
    public function filtroDisponibilidade($status, Request $request)
    {
        $query = Produto::query()
            ->where('ativo', true)
            ->where('quantidade', '>', 0);

        switch ($status) {
            case 'disponiveis':
                $query->where('disponibilidade', 'DISPONIVEL');
                break;
            case 'estoque_baixo':
                $query->where('disponibilidade', 'EST.BAIXO');
                break;
            case 'indisponiveis':
                $query->where('disponibilidade', 'INDISPONIVEL');
                break;
            default:
                // Se o status for inválido, mostra todos
                $query->orderByRaw("
                    CASE 
                        WHEN disponibilidade = 'DISPONIVEL' THEN 1
                        WHEN disponibilidade = 'EST.BAIXO' THEN 2
                        ELSE 3
                    END
                ");
        }

        // Ordenação secundária (igual ao index)
        $orderBy = $request->get('order', 'created_at');
        $allowedOrders = ['created_at', 'valor_unitario', 'descricao', 'categoria', 'quantidade'];
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

        // 🔥 Contagem para exibir na view
        $totais = [
            'disponiveis' => Produto::where('ativo', true)
                ->where('quantidade', '>', 0)
                ->where('disponibilidade', 'DISPONIVEL')
                ->count(),
            'estoque_baixo' => Produto::where('ativo', true)
                ->where('quantidade', '>', 0)
                ->where('disponibilidade', 'EST.BAIXO')
                ->count(),
            'indisponiveis' => Produto::where('ativo', true)
                ->where('quantidade', '>', 0)
                ->where('disponibilidade', 'INDISPONIVEL')
                ->count(),
            'total' => Produto::where('ativo', true)
                ->where('quantidade', '>', 0)
                ->count(),
        ];

        return view('produtos.index', compact('produtos', 'totais'));
    }
}