<?php

namespace App\Http\Controllers;

use App\Models\Banner;
use App\Models\Produto;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class HomeController extends Controller
{
    /**
     * Exibe a página inicial.
     */
    public function index(Request $request)
    {
        // ============================================================
        // BANNERS
        // ============================================================

        $bannersData = Cache::remember(
            'banners_ativos',
            now()->addHour(),
            function () {
                return Banner::ativo()
                    ->ordenado()
                    ->get()
                    ->map(function (Banner $banner) {
                        return [
                            'id' => $banner->id,
                            'titulo' => $banner->titulo,
                            'subtitulo' => $banner->subtitulo,
                            'descricao' => $banner->descricao,
                            'imagem' => $banner->imagem,
                            'tipo' => $banner->tipo,
                            'link' => $banner->link,
                            'texto_botao' => $banner->texto_botao,
                            'cor_fundo' => $banner->cor_fundo,
                            'cor_texto' => $banner->cor_texto,
                            'cor_botao' => $banner->cor_botao,
                        ];
                    })
                    ->values()
                    ->all();
            }
        );

        /*
         * Converte os arrays do cache em objetos simples.
         */
        
        // 🔥 FIX: Valida e normaliza os dados dos banners
        if (is_string($bannersData)) {
            $bannersData = json_decode($bannersData, true) ?: [];
        }
        
        if (!is_array($bannersData)) {
            $bannersData = [];
        }

        $banners = collect($bannersData)
            ->map(function ($banner) {
                // Se for string JSON, decodifica
                if (is_string($banner)) {
                    $banner = json_decode($banner, true);
                }
                
                // Se ainda não for array, pula
                if (!is_array($banner)) {
                    return null;
                }
                
                $obj = (object) $banner;

                // ESTILO DO FUNDO
                $obj->estilo_fundo = null;

                if (!empty($banner['cor_fundo'])) {
                    $corFundo = trim($banner['cor_fundo']);

                    if (str_starts_with($corFundo, '#')) {
                        $obj->estilo_fundo = "background-color: {$corFundo};";
                    } elseif (str_contains($corFundo, 'gradient')) {
                        $obj->estilo_fundo = "background: {$corFundo};";
                    } else {
                        $obj->estilo_fundo = "background: {$corFundo};";
                    }
                }

                // URL DA IMAGEM
                $obj->imagem_url = null;

                if (!empty($banner['imagem'])) {
                    $obj->imagem_url = Storage::disk('public')->url($banner['imagem']);
                }

                return $obj;
            })
            ->filter()
            ->values();

        // ============================================================
        // PRODUTOS EM DESTAQUE
        // ============================================================

        $pageDestaque = max(1, (int) $request->get('page_destaque', 1));

        $produtosDestaque = $this->getCachedPaginator(
            'produtos_destaque',
            Produto::emDestaque(),
            $pageDestaque,
            8,
            'page_destaque'
        );

        // ============================================================
        // OFERTAS DO DIA
        // ============================================================

        $pageOfertas = max(1, (int) $request->get('page_ofertas', 1));

        $ofertas = $this->getCachedPaginator(
            'ofertas_ativas',
            Produto::ofertas(),
            $pageOfertas,
            8,
            'page_ofertas'
        );

        // ============================================================
        // NOVOS PRODUTOS
        // ============================================================

        $pageNovos = max(1, (int) $request->get('page_novos', 1));

        $novosProdutos = $this->getCachedPaginator(
            'novos_produtos',
            Produto::novos(),
            $pageNovos,
            8,
            'page_novos'
        );

        // ============================================================
        // MAIS VENDIDOS
        // ============================================================

        $pageVendidos = max(1, (int) $request->get('page_vendidos', 1));

        $maisVendidos = $this->getCachedPaginator(
            'mais_vendidos',
            Produto::maisVendidos(),
            $pageVendidos,
            8,
            'page_vendidos'
        );

        // ============================================================
        // TODOS OS PRODUTOS
        // ============================================================

        $pageTodos = max(1, (int) $request->get('page_todos', 1));

        $produtosDisponiveis = $this->getCachedPaginator(
            'produtos_disponiveis',
            Produto::todosDisponiveis(),
            $pageTodos,
            12,
            'page_todos'
        );

        // ============================================================
        // VIEW
        // ============================================================

        return view('home', compact(
            'banners',
            'produtosDisponiveis',
            'produtosDestaque',
            'ofertas',
            'novosProdutos',
            'maisVendidos'
        ));
    }

    /**
     * Retorna um paginator utilizando cache.
     *
     * O cache armazena somente dados simples:
     * - arrays
     * - strings
     * - números
     *
     * Não armazenamos o paginator nem Models Eloquent.
     */
    private function getCachedPaginator(
        string $cacheKey,
        $query,
        int $currentPage,
        int $perPage = 12,
        string $pageName = 'page'
    ): LengthAwarePaginator {

        $fullCacheKey = "{$cacheKey}_{$pageName}_{$currentPage}";

        $cachedData = Cache::remember(
            $fullCacheKey,
            now()->addHour(),
            function () use ($query, $perPage, $currentPage, $pageName) {

                $paginator = $query->paginate(
                    $perPage,
                    ['*'],
                    $pageName,
                    $currentPage
                );

                // TRANSFORMA OS MODELS EM ARRAYS
                $items = collect($paginator->items())
                    ->map(function ($produto) {
                        return $produto->toArray();
                    })
                    ->values()
                    ->all();

                // SOMENTE DADOS SIMPLES NO CACHE
                return [
                    'items' => $items,
                    'total' => (int) $paginator->total(),
                    'per_page' => (int) $paginator->perPage(),
                    'current_page' => (int) $paginator->currentPage(),
                    'last_page' => (int) $paginator->lastPage(),
                    'path' => request()->url(),
                    'query' => request()->query(),
                ];
            }
        );

        // 🔥 FIX: Valida e normaliza os dados do cache
        // Se o cache retornou dados corrompidos, força recriação
        if (!is_array($cachedData) || !isset($cachedData['items']) || !is_array($cachedData['items'])) {
            // Força a recriação do cache removendo a chave
            Cache::forget($fullCacheKey);
            
            // Recria o cache chamando a função novamente
            $cachedData = Cache::remember(
                $fullCacheKey,
                now()->addHour(),
                function () use ($query, $perPage, $currentPage, $pageName) {

                    $paginator = $query->paginate(
                        $perPage,
                        ['*'],
                        $pageName,
                        $currentPage
                    );

                    $items = collect($paginator->items())
                        ->map(function ($produto) {
                            return $produto->toArray();
                        })
                        ->values()
                        ->all();

                    return [
                        'items' => $items,
                        'total' => (int) $paginator->total(),
                        'per_page' => (int) $paginator->perPage(),
                        'current_page' => (int) $paginator->currentPage(),
                        'last_page' => (int) $paginator->lastPage(),
                        'path' => request()->url(),
                        'query' => request()->query(),
                    ];
                }
            );
        }

        // 🔥 FIX: Garante que $cachedData['items'] seja um array válido
        $itemsArray = $cachedData['items'] ?? [];
        
        // Se não for array, tenta converter
        if (!is_array($itemsArray)) {
            $itemsArray = [];
        }

        // RECONSTRÓI OS PRODUTOS COMO OBJETOS SIMPLES
        $items = collect($itemsArray)
            ->map(function ($produto) {
                // 🔥 FIX: Se for string JSON, decodifica
                if (is_string($produto)) {
                    $produto = json_decode($produto, true);
                }
                
                // Se for array, converte para objeto
                if (is_array($produto)) {
                    return (object) $produto;
                }
                
                // Se já for objeto, retorna como está
                if (is_object($produto)) {
                    return $produto;
                }
                
                // Fallback: retorna null para itens inválidos
                return null;
            })
            ->filter() // Remove nulos
            ->values() // Reindexa
            ->all();

        // RECRIA O PAGINATOR
        return new LengthAwarePaginator(
            $items,
            $cachedData['total'] ?? 0,
            $cachedData['per_page'] ?? $perPage,
            $cachedData['current_page'] ?? $currentPage,
            [
                'path' => $cachedData['path'] ?? request()->url(),
                'query' => $cachedData['query'] ?? request()->query(),
                'pageName' => $pageName,
            ]
        );
    }

    // ================================================================
    // MÉTODOS CRUD
    // ================================================================

    public function create()
    {
    }

    public function store(Request $request)
    {
    }

    public function show(string $id)
    {
    }

    public function edit(string $id)
    {
    }

    public function update(Request $request, string $id)
    {
    }

    public function destroy(string $id)
    {
    }
}