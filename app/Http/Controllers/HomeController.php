<?php

namespace App\Http\Controllers;

use App\Models\Banner;
use App\Models\Produto;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
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

        if (is_string($bannersData)) {
            $bannersData = json_decode($bannersData, true) ?: [];
        }
        
        if (!is_array($bannersData)) {
            $bannersData = [];
        }

        $banners = collect($bannersData)
            ->map(function ($banner) {
                if (is_string($banner)) {
                    $banner = json_decode($banner, true);
                }
                
                if (!is_array($banner)) {
                    return null;
                }
                
                $obj = (object) $banner;

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

        if (!is_array($cachedData) || !isset($cachedData['items']) || !is_array($cachedData['items'])) {
            Cache::forget($fullCacheKey);
            
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

        $itemsArray = $cachedData['items'] ?? [];
        
        if (!is_array($itemsArray)) {
            $itemsArray = [];
        }

        $items = collect($itemsArray)
            ->map(function ($produto) {
                if (is_string($produto)) {
                    $produto = json_decode($produto, true);
                }
                
                if (is_array($produto)) {
                    return (object) $produto;
                }
                
                if (is_object($produto)) {
                    return $produto;
                }
                
                return null;
            })
            ->filter()
            ->values()
            ->all();

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
    // 🔥 NOVOS MÉTODOS DE CACHE
    // ================================================================

    /**
     * Limpar cache do sistema
     */
    public function clearCache()
    {
        try {
            Artisan::call('view:clear');
            Artisan::call('config:clear');
            Artisan::call('route:clear');
            Artisan::call('event:clear');
            Artisan::call('cache:clear');
            
            // Limpar caches específicos do sistema
            Cache::flush();
            
            Log::info('Cache limpo pelo administrador', [
                'usuario_id' => auth()->id(),
                'email' => auth()->user()->email
            ]);

            return redirect()->back()->with('success', '✅ Cache limpo com sucesso!');
            
        } catch (\Exception $e) {
            Log::error('Erro ao limpar cache: ' . $e->getMessage(), [
                'usuario_id' => auth()->id()
            ]);
            
            return redirect()->back()->with('error', '❌ Erro ao limpar cache: ' . $e->getMessage());
        }
    }

    /**
     * Limpar cache de banners
     */
    public function clearBannerCache()
    {
        try {
            Cache::forget('banners_ativos');
            Cache::forget('banners');
            Cache::forget('banners_active');
            
            Log::info('Cache de banners limpo', [
                'usuario_id' => auth()->id()
            ]);

            return redirect()->back()->with('success', '✅ Cache de banners limpo com sucesso!');
            
        } catch (\Exception $e) {
            Log::error('Erro ao limpar cache de banners: ' . $e->getMessage());
            
            return redirect()->back()->with('error', '❌ Erro ao limpar cache de banners: ' . $e->getMessage());
        }
    }

    /**
     * Recarregar banners
     */
    public function reloadBanners()
    {
        try {
            // Limpar cache
            Cache::forget('banners_ativos');
            Cache::forget('banners');
            Cache::forget('banners_active');
            
            // Recarregar banners
            $banners = Banner::ativo()
                ->ordenado()
                ->get();
            
            Cache::put('banners_ativos', $banners, 3600);
            
            Log::info('Banners recarregados', [
                'usuario_id' => auth()->id(),
                'quantidade' => $banners->count()
            ]);

            return redirect()->back()->with('success', '✅ Banners recarregados com sucesso! (' . $banners->count() . ' banners)');
            
        } catch (\Exception $e) {
            Log::error('Erro ao recarregar banners: ' . $e->getMessage());
            
            return redirect()->back()->with('error', '❌ Erro ao recarregar banners: ' . $e->getMessage());
        }
    }

    /**
     * Limpar cache de produtos
     */
    public function clearProductCache()
    {
        try {
            // Limpar caches de produtos
            Cache::forget('produtos_destaque');
            Cache::forget('ofertas_ativas');
            Cache::forget('novos_produtos');
            Cache::forget('mais_vendidos');
            Cache::forget('produtos_disponiveis');
            
            Log::info('Cache de produtos limpo', [
                'usuario_id' => auth()->id()
            ]);

            return redirect()->back()->with('success', '✅ Cache de produtos limpo com sucesso!');
            
        } catch (\Exception $e) {
            Log::error('Erro ao limpar cache de produtos: ' . $e->getMessage());
            
            return redirect()->back()->with('error', '❌ Erro ao limpar cache de produtos: ' . $e->getMessage());
        }
    }

    /**
     * Limpar todos os caches
     */
    public function clearAllCache()
    {
        try {
            // Limpar cache do Laravel
            Artisan::call('view:clear');
            Artisan::call('config:clear');
            Artisan::call('route:clear');
            Artisan::call('event:clear');
            Artisan::call('cache:clear');
            Artisan::call('optimize:clear');
            
            // Limpar cache do Redis/File
            Cache::flush();
            
            Log::info('Todos os caches limpos', [
                'usuario_id' => auth()->id()
            ]);

            return redirect()->back()->with('success', '✅ Todos os caches foram limpos com sucesso!');
            
        } catch (\Exception $e) {
            Log::error('Erro ao limpar todos os caches: ' . $e->getMessage());
            
            return redirect()->back()->with('error', '❌ Erro ao limpar caches: ' . $e->getMessage());
        }
    }
}