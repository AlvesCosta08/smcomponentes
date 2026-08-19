<?php

namespace App\Http\Controllers;

use App\Models\Produto;
use App\Models\Categoria;
use App\Http\Requests\Produto\BuscarProdutoRequest;
use App\Http\Requests\Produto\FiltroProdutoRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Cache;

class ProdutoController extends Controller
{
    /**
     * Tempo de cache em segundos (1 hora)
     */
    private const CACHE_TTL = 3600;

    /**
     * Listagem de produtos com filtros.
     */
    public function index(FiltroProdutoRequest $request): View|JsonResponse
    {
        $filters = $request->getFilters();
        $ordenacao = $request->getOrdenacao();
        $paginacao = $request->getPaginacao();

        $query = Produto::query()->disponivel();

        // Aplicar filtros
        $query = $this->applyFilters($query, $filters);
        $query = $this->applyOrdenacao($query, $ordenacao);

        $produtos = $query->paginate($paginacao['per_page']);
        
        // Categorias com cache
        $categorias = Cache::remember('categorias_ativas', self::CACHE_TTL, function () {
            return Categoria::ativo()->ordenado()->get();
        });

        if ($request->ajax()) {
            return response()->json([
                'html' => view('produtos.partials.lista', compact('produtos'))->render(),
                'pagination' => (string) $produtos->links(),
                'total' => $produtos->total(),
            ]);
        }

        return view('produtos.index', compact('produtos', 'categorias'));
    }

    /**
     * Detalhe do produto.
     */
    public function show(string $slug): View
    {
        $produto = Produto::with(['categoria', 'imagens'])
            ->where('slug', $slug)
            ->where('ativo', true)
            ->firstOrFail();

        // Incrementar visualizações
        $produto->incrementarVisualizacoes();

        // Produtos relacionados (mesma categoria)
        $relacionados = $this->getProdutosRelacionados($produto);

        // Verificar se está na wishlist (se autenticado)
        $naWishlist = false;
        if (auth()->check()) {
            $naWishlist = auth()->user()->isInWishlist($produto->id);
        }

        return view('produtos.show', compact(
            'produto',
            'relacionados',
            'naWishlist'
        ));
    }

    /**
     * Busca de produtos.
     */
    public function buscar(BuscarProdutoRequest $request): View|RedirectResponse
    {
        $termo = $request->get('q', '');
        $porPagina = $request->get('por_pagina', 12);

        if (empty($termo) || strlen($termo) < 2) {
            return redirect()->route('produtos.index')
                ->with('warning', 'Digite pelo menos 2 caracteres para buscar.');
        }

        $produtos = Produto::disponivel()
            ->buscar($termo)
            ->paginate($porPagina);

        return view('produtos.busca', compact('produtos', 'termo'));
    }

    /**
     * Produtos por categoria.
     */
    public function porCategoria(string $slug): View
    {
        $categoria = Categoria::where('slug', $slug)
            ->where('ativo', true)
            ->firstOrFail();

        $produtos = Produto::disponivel()
            ->where('categoria_id', $categoria->id)
            ->paginate(12);

        return view('produtos.categoria', compact('produtos', 'categoria'));
    }

    /**
     * Filtro por disponibilidade.
     */
    public function filtroDisponibilidade(string $status): View
    {
        $query = Produto::where('ativo', true);
        $titulo = 'Produtos';

        // Aplicar filtro de status
        $query = $this->applyStatusFilter($query, $status);
        $titulo = $this->getStatusTitle($status);

        $produtos = $query->paginate(12);
        
        $categorias = Cache::remember('categorias_ativas', self::CACHE_TTL, function () {
            return Categoria::ativo()->ordenado()->get();
        });

        return view('produtos.index', compact('produtos', 'categorias', 'titulo'));
    }

    /**
     * API: Produtos em destaque.
     */
    public function destaques(): JsonResponse
    {
        $produtos = Cache::remember('api_produtos_destaque', self::CACHE_TTL, function () {
            return Produto::emDestaque()
                ->limit(6)
                ->get()
                ->map(fn($p) => $this->formatProdutoApi($p));
        });

        return response()->json($produtos);
    }

    /**
     * API: Produtos em oferta.
     */
    public function ofertas(): JsonResponse
    {
        $produtos = Cache::remember('api_produtos_ofertas', self::CACHE_TTL, function () {
            return Produto::ofertas()
                ->limit(6)
                ->get()
                ->map(fn($p) => $this->formatProdutoApi($p));
        });

        return response()->json($produtos);
    }

    /**
     * API: Produtos novos.
     */
    public function novos(): JsonResponse
    {
        $produtos = Cache::remember('api_produtos_novos', self::CACHE_TTL, function () {
            return Produto::novos()
                ->limit(6)
                ->get()
                ->map(fn($p) => $this->formatProdutoApi($p));
        });

        return response()->json($produtos);
    }

    /**
     * API: Produtos mais vendidos.
     */
    public function maisVendidos(): JsonResponse
    {
        $produtos = Cache::remember('api_produtos_mais_vendidos', self::CACHE_TTL, function () {
            return Produto::maisVendidos()
                ->limit(6)
                ->get()
                ->map(fn($p) => $this->formatProdutoApi($p));
        });

        return response()->json($produtos);
    }

    // ================================================================
    // MÉTODOS PRIVADOS
    // ================================================================

    /**
     * Aplica filtros à query.
     */
    private function applyFilters($query, array $filters)
    {
        if (!empty($filters['categoria_id'])) {
            $query->where('categoria_id', $filters['categoria_id']);
        }

        if (!empty($filters['status'])) {
            $query = $this->applyStatusFilter($query, $filters['status']);
        }

        // Filtro de preço
        if (!empty($filters['preco_min'])) {
            $query->where(function($q) use ($filters) {
                $q->where('valor_atacado', '>=', $filters['preco_min'])
                  ->orWhere('valor_unitario', '>=', $filters['preco_min']);
            });
        }

        if (!empty($filters['preco_max'])) {
            $query->where(function($q) use ($filters) {
                $q->where('valor_atacado', '<=', $filters['preco_max'])
                  ->orWhere('valor_unitario', '<=', $filters['preco_max']);
            });
        }

        // Filtros booleanos
        if (!empty($filters['destaque'])) {
            $query->where('destaque', true);
        }

        if (!empty($filters['novo'])) {
            $query->where('novo', true);
        }

        if (!empty($filters['mais_vendido'])) {
            $query->where('mais_vendido', true);
        }

        // Busca
        if (!empty($filters['busca'])) {
            $query->buscar($filters['busca']);
        }

        return $query;
    }

    /**
     * Aplica ordenação à query.
     */
    private function applyOrdenacao($query, array $ordenacao)
    {
        $campo = $ordenacao['campo'];
        $direcao = $ordenacao['direcao'];

        // Se for ordenação especial (scopes)
        if ($campo === 'novos') {
            return $query->novos();
        }

        if ($campo === 'destaque') {
            return $query->emDestaque();
        }

        if ($campo === 'mais_vendidos') {
            return $query->maisVendidos();
        }

        return $query->orderBy($campo, $direcao);
    }

    /**
     * Aplica filtro de status.
     */
    private function applyStatusFilter($query, string $status)
    {
        switch ($status) {
            case 'disponivel':
                return $query->disponivel();
            case 'estoque_baixo':
                return $query->baixoEstoque();
            case 'indisponivel':
                return $query->where('quantidade', 0)
                    ->orWhere('disponibilidade', Produto::INDISPONIVEL);
            default:
                return $query;
        }
    }

    /**
     * Retorna o título do status.
     */
    private function getStatusTitle(string $status): string
    {
        return match ($status) {
            'disponivel' => 'Produtos Disponíveis',
            'estoque_baixo' => 'Produtos com Estoque Baixo',
            'indisponivel' => 'Produtos Indisponíveis',
            default => 'Produtos Disponíveis',
        };
    }

    /**
     * Obtém produtos relacionados.
     */
    private function getProdutosRelacionados(Produto $produto)
    {
        if (!$produto->categoria_id) {
            return new \Illuminate\Database\Eloquent\Collection();
        }

        return Cache::remember(
            "produtos_relacionados_{$produto->id}",
            self::CACHE_TTL,
            function () use ($produto) {
                return Produto::disponivel()
                    ->where('categoria_id', $produto->categoria_id)
                    ->where('id', '!=', $produto->id)
                    ->limit(4)
                    ->get();
            }
        );
    }

    /**
     * Formata produto para API.
     */
    private function formatProdutoApi(Produto $produto): array
    {
        return [
            'id' => $produto->id,
            'descricao' => $produto->descricao,
            'slug' => $produto->slug,
            'imagem' => $produto->imagem_url,
            'preco' => $produto->preco_formatado,
            'preco_promocional' => $produto->preco_promocional_formatado,
            'tem_promocao' => $produto->tem_promocao,
            'categoria' => $produto->categoria?->nome,
            'link' => route('produtos.show', $produto->slug),
        ];
    }
}