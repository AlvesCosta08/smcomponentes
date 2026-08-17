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
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;

class ProdutoController extends Controller
{
    /**
     * Tempo de cache em segundos para buscas (5 minutos)
     * Buscas são mais dinâmicas, cache mais curto
     */
    private const CACHE_TTL_BUSCA = 300;

    /**
     * Tempo de cache em segundos para dados estáticos (1 hora)
     */
    private const CACHE_TTL_LONGO = 3600;

    /**
     * Listagem de produtos com filtros.
     */
    public function index(FiltroProdutoRequest $request): View|JsonResponse
    {
        try {
            $filters = $request->getFilters() ?? [];
            $ordenacao = $request->getOrdenacao() ?? ['campo' => 'id', 'direcao' => 'desc'];
            $paginacao = $request->getPaginacao() ?? ['per_page' => 12];

            $query = Produto::query()->disponivel();

            // Aplicar filtros
            $query = $this->applyFilters($query, $filters);
            $query = $this->applyOrdenacao($query, $ordenacao);

            $produtos = $query->paginate($paginacao['per_page']);
            
            // Categorias com cache (mais longo pois muda pouco)
            $categorias = Cache::remember('categorias_ativas', self::CACHE_TTL_LONGO, function () {
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
            
        } catch (\Exception $e) {
            Log::error('Erro ao listar produtos: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
            
            if ($request->ajax()) {
                return response()->json([
                    'error' => 'Erro ao carregar produtos. Tente novamente.'
                ], 500);
            }
            
            return back()->with('error', 'Erro ao carregar produtos. Tente novamente.');
        }
    }

    /**
     * Detalhe do produto.
     */
    public function show(string $slug): View|RedirectResponse
    {
        try {
            $produto = Produto::with(['categoria', 'imagens'])
                ->where('slug', $slug)
                ->where('ativo', true)
                ->firstOrFail();

            // Incrementar visualizações (verifica se método existe)
            if (method_exists($produto, 'incrementarVisualizacoes')) {
                $produto->incrementarVisualizacoes();
            }

            // Produtos relacionados (mesma categoria)
            $relacionados = $this->getProdutosRelacionados($produto);

            // Verificar se está na wishlist (se autenticado e método existe)
            $naWishlist = false;
            if (auth()->check() && method_exists(auth()->user(), 'isInWishlist')) {
                $naWishlist = auth()->user()->isInWishlist($produto->id);
            }

            return view('produtos.show', compact(
                'produto',
                'relacionados',
                'naWishlist'
            ));
            
        } catch (ModelNotFoundException $e) {
            Log::warning('Produto não encontrado: ' . $slug);
            return redirect()->route('produtos.index')
                ->with('error', 'Produto não encontrado.');
                
        } catch (\Exception $e) {
            Log::error('Erro ao exibir produto: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
            return redirect()->route('produtos.index')
                ->with('error', 'Erro ao carregar produto. Tente novamente.');
        }
    }

    /**
     * Busca de produtos com suporte a AJAX (Live Search)
     */
    public function buscar(Request $request): View|JsonResponse|RedirectResponse
    {
        try {
            $termo = trim($request->get('q', ''));
            $porPagina = (int) $request->get('por_pagina', 12);

            // Validar paginação
            if ($porPagina < 1 || $porPagina > 100) {
                $porPagina = 12;
            }

            // Se não houver termo, retorna resultados vazios
            if (empty($termo)) {
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json([
                        'html' => $this->getEmptyResultHtml('Digite algo para buscar'),
                        'total' => 0,
                        'termo' => ''
                    ]);
                }
                return view('produtos.busca', [
                    'produtos' => new Collection(),
                    'termo' => ''
                ]);
            }

            // Se termo tiver menos de 2 caracteres
            if (strlen($termo) < 2) {
                $mensagem = 'Digite pelo menos 2 caracteres para buscar.';
                
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json([
                        'html' => $this->getEmptyResultHtml($mensagem),
                        'total' => 0,
                        'termo' => $termo
                    ]);
                }
                
                return view('produtos.busca', [
                    'produtos' => new Collection(),
                    'termo' => $termo,
                    'mensagem' => $mensagem
                ]);
            }

            // Buscar produtos com cache para buscas frequentes
            $cacheKey = 'busca_' . md5($termo . '_' . $porPagina . '_' . $request->get('page', 1));
            
            $produtos = Cache::remember($cacheKey, self::CACHE_TTL_BUSCA, function () use ($termo, $porPagina) {
                return Produto::disponivel()
                    ->buscar($termo)
                    ->paginate($porPagina);
            });

            // Se for requisição AJAX
            if ($request->ajax() || $request->wantsJson()) {
                try {
                    $html = view('produtos.partials.lista', [
                        'produtos' => $produtos,
                        'termo' => $termo
                    ])->render();
                } catch (\Exception $e) {
                    Log::error('Erro ao renderizar partial: ' . $e->getMessage());
                    $html = $this->getEmptyResultHtml('Erro ao carregar resultados.');
                }
                
                return response()->json([
                    'html' => $html,
                    'total' => $produtos->total(),
                    'termo' => $termo,
                    'paginas' => $produtos->lastPage(),
                    'pagina_atual' => $produtos->currentPage(),
                    'por_pagina' => $produtos->perPage()
                ]);
            }

            return view('produtos.busca', compact('produtos', 'termo'));
            
        } catch (ValidationException $e) {
            Log::warning('Erro de validação na busca: ' . $e->getMessage());
            
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'html' => $this->getEmptyResultHtml('Termo de busca inválido.'),
                    'total' => 0,
                    'termo' => $request->get('q', '')
                ], 422);
            }
            
            return redirect()->route('produtos.index')
                ->with('error', 'Termo de busca inválido.');
                
        } catch (\Exception $e) {
            Log::error('Erro na busca: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
            
            $mensagemErro = 'Erro ao realizar a busca. Tente novamente.';
            
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'html' => $this->getEmptyResultHtml($mensagemErro),
                    'total' => 0,
                    'termo' => $request->get('q', ''),
                    'error' => $mensagemErro
                ], 500);
            }
            
            return view('produtos.busca', [
                'produtos' => new Collection(),
                'termo' => $request->get('q', ''),
                'erro' => $mensagemErro
            ]);
        }
    }

    /**
     * Produtos por categoria.
     */
    public function porCategoria(string $slug): View|RedirectResponse
    {
        try {
            $categoria = Categoria::where('slug', $slug)
                ->where('ativo', true)
                ->firstOrFail();

            $produtos = Produto::disponivel()
                ->where('categoria_id', $categoria->id)
                ->paginate(12);

            return view('produtos.categoria', compact('produtos', 'categoria'));
            
        } catch (ModelNotFoundException $e) {
            Log::warning('Categoria não encontrada: ' . $slug);
            return redirect()->route('produtos.index')
                ->with('error', 'Categoria não encontrada.');
                
        } catch (\Exception $e) {
            Log::error('Erro ao listar produtos por categoria: ' . $e->getMessage());
            return redirect()->route('produtos.index')
                ->with('error', 'Erro ao carregar produtos da categoria.');
        }
    }

    /**
     * Filtro por disponibilidade.
     */
    public function filtroDisponibilidade(string $status): View|RedirectResponse
    {
        try {
            $statusValidos = ['disponivel', 'estoque_baixo', 'indisponivel'];
            
            if (!in_array($status, $statusValidos)) {
                return redirect()->route('produtos.index')
                    ->with('error', 'Status inválido.');
            }

            $query = Produto::where('ativo', true);
            $titulo = 'Produtos';

            // Aplicar filtro de status
            $query = $this->applyStatusFilter($query, $status);
            $titulo = $this->getStatusTitle($status);

            $produtos = $query->paginate(12);
            
            $categorias = Cache::remember('categorias_ativas', self::CACHE_TTL_LONGO, function () {
                return Categoria::ativo()->ordenado()->get();
            });

            return view('produtos.index', compact('produtos', 'categorias', 'titulo'));
            
        } catch (\Exception $e) {
            Log::error('Erro ao filtrar por disponibilidade: ' . $e->getMessage());
            return redirect()->route('produtos.index')
                ->with('error', 'Erro ao aplicar filtro.');
        }
    }

    /**
     * API: Produtos em destaque.
     */
    public function destaques(): JsonResponse
    {
        try {
            $produtos = Cache::remember('api_produtos_destaque', self::CACHE_TTL_LONGO, function () {
                return Produto::emDestaque()
                    ->limit(6)
                    ->get()
                    ->map(fn($p) => $this->formatProdutoApi($p));
            });

            return response()->json([
                'success' => true,
                'data' => $produtos
            ]);
            
        } catch (\Exception $e) {
            Log::error('Erro ao buscar destaques: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erro ao carregar produtos em destaque.'
            ], 500);
        }
    }

    /**
     * API: Produtos em oferta.
     */
    public function ofertas(): JsonResponse
    {
        try {
            $produtos = Cache::remember('api_produtos_ofertas', self::CACHE_TTL_LONGO, function () {
                return Produto::ofertas()
                    ->limit(6)
                    ->get()
                    ->map(fn($p) => $this->formatProdutoApi($p));
            });

            return response()->json([
                'success' => true,
                'data' => $produtos
            ]);
            
        } catch (\Exception $e) {
            Log::error('Erro ao buscar ofertas: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erro ao carregar produtos em oferta.'
            ], 500);
        }
    }

    /**
     * API: Produtos novos.
     */
    public function novos(): JsonResponse
    {
        try {
            $produtos = Cache::remember('api_produtos_novos', self::CACHE_TTL_LONGO, function () {
                return Produto::novos()
                    ->limit(6)
                    ->get()
                    ->map(fn($p) => $this->formatProdutoApi($p));
            });

            return response()->json([
                'success' => true,
                'data' => $produtos
            ]);
            
        } catch (\Exception $e) {
            Log::error('Erro ao buscar novos produtos: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erro ao carregar produtos novos.'
            ], 500);
        }
    }

    /**
     * API: Produtos mais vendidos.
     */
    public function maisVendidos(): JsonResponse
    {
        try {
            $produtos = Cache::remember('api_produtos_mais_vendidos', self::CACHE_TTL_LONGO, function () {
                return Produto::maisVendidos()
                    ->limit(6)
                    ->get()
                    ->map(fn($p) => $this->formatProdutoApi($p));
            });

            return response()->json([
                'success' => true,
                'data' => $produtos
            ]);
            
        } catch (\Exception $e) {
            Log::error('Erro ao buscar mais vendidos: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erro ao carregar produtos mais vendidos.'
            ], 500);
        }
    }

    // ================================================================
    // MÉTODOS PRIVADOS
    // ================================================================

    /**
     * Retorna HTML para resultados vazios.
     */
    private function getEmptyResultHtml(string $mensagem): string
    {
        $route = route('produtos.index');
        return <<<HTML
        <div class="row">
            <div class="col-12 text-center py-5">
                <i class="bi bi-search display-1 text-muted"></i>
                <h3 class="mt-3">{$mensagem}</h3>
                <p class="text-muted">Tente buscar por outro termo ou navegue pelas categorias.</p>
                <a href="{$route}" class="btn btn-outline-primary mt-3">
                    Ver todos os produtos
                </a>
            </div>
        </div>
        HTML;
    }

    /**
     * Aplica filtros à query.
     */
    private function applyFilters($query, array $filters): object
    {
        try {
            if (empty($filters)) {
                return $query;
            }

            if (!empty($filters['categoria_id']) && is_numeric($filters['categoria_id'])) {
                $query->where('categoria_id', (int) $filters['categoria_id']);
            }

            if (!empty($filters['status'])) {
                $query = $this->applyStatusFilter($query, $filters['status']);
            }

            // Filtro de preço
            if (!empty($filters['preco_min']) && is_numeric($filters['preco_min'])) {
                $precoMin = (float) $filters['preco_min'];
                $query->where(function($q) use ($precoMin) {
                    $q->where('valor_atacado', '>=', $precoMin)
                      ->orWhere('valor_unitario', '>=', $precoMin);
                });
            }

            if (!empty($filters['preco_max']) && is_numeric($filters['preco_max'])) {
                $precoMax = (float) $filters['preco_max'];
                $query->where(function($q) use ($precoMax) {
                    $q->where('valor_atacado', '<=', $precoMax)
                      ->orWhere('valor_unitario', '<=', $precoMax);
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
            if (!empty($filters['busca']) && is_string($filters['busca'])) {
                $query->buscar(trim($filters['busca']));
            }

            return $query;
            
        } catch (\Exception $e) {
            Log::error('Erro ao aplicar filtros: ' . $e->getMessage());
            return $query;
        }
    }

    /**
     * Aplica ordenação à query.
     */
    private function applyOrdenacao($query, array $ordenacao): object
    {
        try {
            $campo = $ordenacao['campo'] ?? 'id';
            $direcao = $ordenacao['direcao'] ?? 'desc';

            // Validar direção
            $direcao = strtolower($direcao);
            if (!in_array($direcao, ['asc', 'desc'])) {
                $direcao = 'desc';
            }

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

            // Verifica se o campo existe na tabela antes de ordenar
            $fillable = (new Produto())->getFillable();
            if (in_array($campo, $fillable)) {
                return $query->orderBy($campo, $direcao);
            }

            return $query->orderBy('id', 'desc');
            
        } catch (\Exception $e) {
            Log::error('Erro ao aplicar ordenação: ' . $e->getMessage());
            return $query->orderBy('id', 'desc');
        }
    }

    /**
     * Aplica filtro de status.
     */
    private function applyStatusFilter($query, string $status): object
    {
        try {
            switch ($status) {
                case 'disponivel':
                    return $query->disponivel();
                case 'estoque_baixo':
                    return $query->baixoEstoque();
                case 'indisponivel':
                    return $query->where('quantidade', 0)
                        ->orWhere('disponibilidade', 'indisponivel');
                default:
                    return $query;
            }
        } catch (\Exception $e) {
            Log::error('Erro ao aplicar filtro de status: ' . $e->getMessage());
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
     * 
     * @return \Illuminate\Database\Eloquent\Collection
     */
    private function getProdutosRelacionados(Produto $produto): Collection
    {
        try {
            $cacheKey = "produtos_relacionados_{$produto->id}";
            
            $relacionados = Cache::remember($cacheKey, self::CACHE_TTL_LONGO, function () use ($produto) {
                // Se tem categoria, busca produtos da mesma categoria
                if ($produto->categoria_id) {
                    $relacionados = Produto::disponivel()
                        ->where('categoria_id', $produto->categoria_id)
                        ->where('id', '!=', $produto->id)
                        ->limit(4)
                        ->get();
                    
                    // Se encontrou produtos, retorna eles
                    if ($relacionados->isNotEmpty()) {
                        return $relacionados;
                    }
                }
                
                // Fallback: produtos aleatórios
                return Produto::disponivel()
                    ->where('id', '!=', $produto->id)
                    ->inRandomOrder()
                    ->limit(4)
                    ->get();
            });
            
            // Garantir que o retorno é sempre uma Collection do Eloquent
            if (!$relacionados instanceof Collection) {
                return new Collection($relacionados->toArray());
            }
            
            return $relacionados;
            
        } catch (\Exception $e) {
            Log::error('Erro ao buscar produtos relacionados: ' . $e->getMessage());
            return new Collection();
        }
    }

    /**
     * Formata produto para API.
     */
    private function formatProdutoApi(Produto $produto): array
    {
        try {
            return [
                'id' => $produto->id,
                'descricao' => $produto->descricao,
                'slug' => $produto->slug,
                'imagem' => $produto->imagem_url ?? asset('images/produto-placeholder.jpg'),
                'preco' => $produto->preco_formatado ?? 'R$ 0,00',
                'preco_promocional' => $produto->preco_promocional_formatado ?? '',
                'tem_promocao' => $produto->tem_promocao ?? false,
                'categoria' => $produto->categoria?->nome ?? 'Sem categoria',
                'link' => route('produtos.show', $produto->slug ?? $produto->id),
                'disponivel' => $produto->disponivel ?? false,
            ];
        } catch (\Exception $e) {
            Log::error('Erro ao formatar produto para API: ' . $e->getMessage());
            return [
                'id' => $produto->id ?? 0,
                'descricao' => 'Produto indisponível',
                'slug' => '',
                'imagem' => asset('images/produto-placeholder.jpg'),
                'preco' => 'R$ 0,00',
                'preco_promocional' => '',
                'tem_promocao' => false,
                'categoria' => 'Sem categoria',
                'link' => '#',
                'disponivel' => false,
            ];
        }
    }

    /**
     * Limpa o cache de produtos.
     */
    public function clearCache(): JsonResponse
    {
        try {
            // Limpar caches específicos
            Cache::forget('categorias_ativas');
            Cache::forget('api_produtos_destaque');
            Cache::forget('api_produtos_ofertas');
            Cache::forget('api_produtos_novos');
            Cache::forget('api_produtos_mais_vendidos');
            
            // Limpar caches de busca (prefixo busca_)
            $cacheKeys = Cache::get('busca_keys', []);
            foreach ($cacheKeys as $key) {
                Cache::forget($key);
            }
            Cache::forget('busca_keys');
            
            Log::info('Cache de produtos limpo manualmente');
            
            return response()->json([
                'success' => true,
                'message' => 'Cache limpo com sucesso.'
            ]);
            
        } catch (\Exception $e) {
            Log::error('Erro ao limpar cache: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erro ao limpar cache.'
            ], 500);
        }
    }
}