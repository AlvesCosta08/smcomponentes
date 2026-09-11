<?php

namespace App\Http\Controllers;

use App\Models\Produto;
use App\Models\Categoria;
use App\Http\Requests\Produto\BuscarProdutoRequest;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

class ProdutoController extends Controller
{
    private const CACHE_TTL = 3600;
    private const PER_PAGE = 12;

    // ================================================================
    // LISTAGEM
    // ================================================================

    /**
     * Listagem de produtos com filtros e ordenação.
     */
    public function index(Request $request): View|JsonResponse
    {
        $query = Produto::query()->with(['categoria']);

        // Filtros
        $this->aplicarFiltros($query, $request);

        // Ordenação
        $this->aplicarOrdenacao($query, $request);

        // Paginação
        $perPage = (int) $request->get('per_page', self::PER_PAGE);
        $produtos = $query->paginate($perPage)->withQueryString();

        // Totais para os filtros
        $totais = $this->calcularTotais();

        // Categorias para o filtro
        $categorias = Cache::remember('categorias_ativas', self::CACHE_TTL, function () {
            return Categoria::where('ativo', true)->orderBy('nome')->get();
        });

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'html' => view('produtos.partials.lista', compact('produtos'))->render(),
                'pagination' => (string) $produtos->links(),
                'total' => $produtos->total(),
            ]);
        }

        return view('produtos.index', compact('produtos', 'totais', 'categorias'));
    }

    // ================================================================
    // DETALHE
    // ================================================================

    /**
     * Detalhe do produto.
     */
    public function show(string $slug): View
    {
        $produto = Produto::with(['categoria', 'imagens'])
            ->where('slug', $slug)
            ->where('ativo', true)
            ->firstOrFail();

        $produto->incrementarVisualizacoes();

        // Produtos relacionados
        $relacionados = collect();
        if ($produto->categoria_id) {
            $relacionados = Produto::query()
                ->with('categoria')
                ->where('ativo', true)
                ->where('status', 'disponivel')
                ->where('quantidade', '>', 0)
                ->where('categoria_id', $produto->categoria_id)
                ->where('id', '!=', $produto->id)
                ->limit(4)
                ->get();
        }

        // Wishlist
        $naWishlist = false;
        if (auth()->check() && method_exists(auth()->user(), 'isInWishlist')) {
            $naWishlist = auth()->user()->isInWishlist($produto->id);
        }

        return view('produtos.show', compact('produto', 'relacionados', 'naWishlist'));
    }

    // ================================================================
    // BUSCA
    // ================================================================

    /**
     * Busca de produtos.
     */
    public function buscar(BuscarProdutoRequest $request): View|RedirectResponse
    {
        $termo = $request->getTermo();
        $perPage = $request->getPorPagina();

        if (empty($termo) || mb_strlen($termo) < 2) {
            return redirect()->route('produtos.index')
                ->with('warning', 'Digite pelo menos 2 caracteres para buscar.');
        }

        $produtos = Produto::query()
            ->with('categoria')
            ->where('ativo', true)
            ->buscar($termo)
            ->orderBy('descricao')
            ->paginate($perPage)
            ->withQueryString();

        return view('produtos.busca', compact('produtos', 'termo'));
    }

    // ================================================================
    // CATEGORIA
    // ================================================================

    /**
     * Produtos por categoria.
     * Aceita SLUG ou ID.
     */
    public function porCategoria(string $categoria): View
    {
        // Tenta por slug primeiro
        $categoriaModel = Categoria::where('slug', $categoria)
            ->where('ativo', true)
            ->first();

        // Se não achou e for numérico, busca por ID
        if (!$categoriaModel && is_numeric($categoria)) {
            $categoriaModel = Categoria::where('id', (int) $categoria)
                ->where('ativo', true)
                ->first();
        }

        if (!$categoriaModel) {
            abort(404, 'Categoria não encontrada');
        }

        $produtos = Produto::query()
            ->with('categoria')
            ->where('ativo', true)
            ->where('status', 'disponivel')
            ->where('quantidade', '>', 0)
            ->where('categoria_id', $categoriaModel->id)
            ->orderBy('descricao')
            ->paginate(self::PER_PAGE)
            ->withQueryString();

        $categorias = Cache::remember('categorias_ativas', self::CACHE_TTL, function () {
            return Categoria::where('ativo', true)->orderBy('nome')->get();
        });

        return view('produtos.categoria', compact('produtos', 'categoria', 'categorias'));
    }

    // ================================================================
    // FILTRO POR STATUS
    // ================================================================

    /**
     * Filtro por status de disponibilidade.
     */
    public function filtroDisponibilidade(string $status): View
    {
        $query = Produto::query()->with('categoria')->where('ativo', true);
        $titulo = 'Produtos';

        switch ($status) {
            case 'disponivel':
                $query->where('status', 'disponivel')
                      ->where('quantidade', '>', 0);
                $titulo = 'Produtos Disponíveis';
                break;

            case 'indisponivel':
                $query->where(function ($q) {
                    $q->where('status', 'indisponivel')
                      ->orWhere('quantidade', '<=', 0);
                });
                $titulo = 'Produtos Indisponíveis';
                break;

            case 'estoque_baixo':
                $query->where('quantidade', '>', 0)
                      ->whereRaw('quantidade <= COALESCE(estoque_minimo, 5)');
                $titulo = 'Produtos com Estoque Baixo';
                break;

            case 'sob_encomenda':
                $query->where('status', 'sob_encomenda');
                $titulo = 'Produtos Sob Encomenda';
                break;

            default:
                abort(404);
        }

        $produtos = $query->orderBy('descricao')
            ->paginate(self::PER_PAGE)
            ->withQueryString();

        $totais = $this->calcularTotais();

        $categorias = Cache::remember('categorias_ativas', self::CACHE_TTL, function () {
            return Categoria::where('ativo', true)->orderBy('nome')->get();
        });

        return view('produtos.index', compact('produtos', 'totais', 'categorias', 'titulo'));
    }

    // ================================================================
    // APIs
    // ================================================================

    public function destaques(): JsonResponse
    {
        $produtos = Cache::remember('api_produtos_destaque', self::CACHE_TTL, function () {
            return Produto::emDestaque()->limit(6)->get()
                ->map(fn($p) => $this->formatProdutoApi($p));
        });

        return response()->json($produtos);
    }

    public function ofertas(): JsonResponse
    {
        $produtos = Cache::remember('api_produtos_ofertas', self::CACHE_TTL, function () {
            return Produto::ofertas()->limit(6)->get()
                ->map(fn($p) => $this->formatProdutoApi($p));
        });

        return response()->json($produtos);
    }

    public function novos(): JsonResponse
    {
        $produtos = Cache::remember('api_produtos_novos', self::CACHE_TTL, function () {
            return Produto::novos()->limit(6)->get()
                ->map(fn($p) => $this->formatProdutoApi($p));
        });

        return response()->json($produtos);
    }

    public function maisVendidos(): JsonResponse
    {
        $produtos = Cache::remember('api_produtos_mais_vendidos', self::CACHE_TTL, function () {
            return Produto::maisVendidos()->limit(6)->get()
                ->map(fn($p) => $this->formatProdutoApi($p));
        });

        return response()->json($produtos);
    }

    // ================================================================
    // MÉTODOS PRIVADOS
    // ================================================================

    /**
     * Aplica filtros do request.
     */
    private function aplicarFiltros($query, Request $request): void
    {
        // Filtro por categoria
        if ($request->filled('categoria')) {
            $query->where('categoria_id', $request->categoria);
        }

        // Filtro por status
        if ($request->filled('status')) {
            switch ($request->status) {
                case 'disponivel':
                    $query->where('status', 'disponivel')
                          ->where('quantidade', '>', 0);
                    break;
                case 'indisponivel':
                    $query->where(function ($q) {
                        $q->where('status', 'indisponivel')
                          ->orWhere('quantidade', '<=', 0);
                    });
                    break;
                case 'estoque_baixo':
                    $query->where('quantidade', '>', 0)
                          ->whereRaw('quantidade <= COALESCE(estoque_minimo, 5)');
                    break;
                case 'sob_encomenda':
                    $query->where('status', 'sob_encomenda');
                    break;
            }
        }

        // Filtro por preço (usa valor_atacado com fallback)
        if ($request->filled('preco_min')) {
            $query->whereRaw(
                'COALESCE(valor_atacado, valor_unitario) >= ?',
                [(float) $request->preco_min]
            );
        }
        if ($request->filled('preco_max')) {
            $query->whereRaw(
                'COALESCE(valor_atacado, valor_unitario) <= ?',
                [(float) $request->preco_max]
            );
        }

        // Filtros booleanos
        if ($request->filled('destaque')) {
            $query->where('destaque', true);
        }
        if ($request->filled('novo')) {
            $query->where('novo', true);
        }
        if ($request->filled('mais_vendido')) {
            $query->where('mais_vendido', true);
        }

        // Busca textual
        if ($request->filled('q')) {
            $query->buscar($request->q);
        }
    }

    /**
     * Aplica ordenação.
     */
    private function aplicarOrdenacao($query, Request $request): void
    {
        $campo = (string) $request->get('order', 'created_at');
        $dir = (string) $request->get('dir', 'desc');
        $dir = in_array($dir, ['asc', 'desc'], true) ? $dir : 'desc';

        switch ($campo) {
            case 'valor_atacado':
            case 'valor_unitario':
            case 'preco':
                $query->orderByRaw("COALESCE(valor_atacado, valor_unitario) {$dir}");
                break;

            case 'descricao':
            case 'referencia':
            case 'visualizacoes':
            case 'created_at':
                $query->orderBy($campo, $dir);
                break;

            default:
                $query->orderBy('created_at', 'desc');
        }
    }

    /**
     * Calcula totais para os filtros.
     */
    private function calcularTotais(): array
    {
        return [
            'total' => Produto::where('ativo', true)->count(),
            'disponiveis' => Produto::where('ativo', true)
                ->where('status', 'disponivel')
                ->where('quantidade', '>', 0)
                ->count(),
            'indisponiveis' => Produto::where('ativo', true)
                ->where(function ($q) {
                    $q->where('status', 'indisponivel')
                      ->orWhere('quantidade', '<=', 0);
                })
                ->count(),
            'estoque_baixo' => Produto::where('ativo', true)
                ->where('quantidade', '>', 0)
                ->whereRaw('quantidade <= COALESCE(estoque_minimo, 5)')
                ->count(),
            'sob_encomenda' => Produto::where('ativo', true)
                ->where('status', 'sob_encomenda')
                ->count(),
        ];
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
            'preco' => $produto->preco_atacado_formatado,
            'preco_unitario' => $produto->preco_formatado,
            'preco_promocional' => $produto->preco_promocional_formatado,
            'tem_promocao' => $produto->tem_promocao,
            'desconto_percentual' => $produto->desconto_percentual,
            'status' => $produto->status,
            'categoria' => $produto->categoria?->nome,
            'link' => route('produtos.show', $produto->slug),
        ];
    }
}