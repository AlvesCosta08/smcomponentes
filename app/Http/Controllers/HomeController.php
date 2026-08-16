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
        // BANNERS - CORRIGIDO DEFINITIVAMENTE
        // ============================================================

        // Buscar banners do banco
        $bannersFromDb = Banner::ativo()
            ->ordenado()
            ->get();

        // Criar a variável $banners como Collection de stdClass
        $banners = collect();

        if ($bannersFromDb->isEmpty()) {
            // Banner padrão
            $bannerDefault = new \stdClass();
            $bannerDefault->id = null;
            $bannerDefault->titulo = 'SM Componentes';
            $bannerDefault->subtitulo = 'Qualidade em Componentes Eletrônicos';
            $bannerDefault->descricao = 'Encontre os melhores componentes para seus projetos';
            $bannerDefault->imagem_url = null;
            $bannerDefault->link = route('produtos.index');
            $bannerDefault->texto_botao = 'Ver Produtos';
            $bannerDefault->cor_texto = '#ffffff';
            $bannerDefault->cor_botao = 'light';
            $bannerDefault->estilo_fundo = 'background: linear-gradient(135deg, #0b1a33 0%, #1a3a5c 100%);';
            
            $banners->push($bannerDefault);
        } else {
            // Converter cada banner para stdClass
            foreach ($bannersFromDb as $banner) {
                $obj = new \stdClass();
                $obj->id = $banner->id;
                $obj->titulo = $banner->titulo;
                $obj->subtitulo = $banner->subtitulo;
                $obj->descricao = $banner->descricao;
                $obj->imagem_url = $this->getImageUrl($banner->imagem);
                $obj->link = $banner->link;
                $obj->texto_botao = $banner->texto_botao;
                $obj->cor_texto = $banner->cor_texto ?? '#ffffff';
                $obj->cor_botao = $banner->cor_botao ?? 'primary';
                $obj->estilo_fundo = $this->getEstiloFundo($banner->cor_fundo);
                
                $banners->push($obj);
            }
        }

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
     * Método auxiliar: Gerar URL da imagem
     */
    private function getImageUrl($imagem)
    {
        if (empty($imagem)) {
            return null;
        }

        // Se já for URL completa (http/https)
        if (filter_var($imagem, FILTER_VALIDATE_URL)) {
            return $imagem;
        }

        // Remove o prefixo 'banners/' se já existir
        $cleanPath = str_replace('banners/', '', $imagem);
        $storagePath = 'banners/' . $cleanPath;

        // Verifica se o arquivo existe no storage
        if (Storage::disk('public')->exists($storagePath)) {
            return Storage::disk('public')->url($storagePath);
        }

        // Fallback
        return asset('storage/' . $storagePath);
    }

    /**
     * Método auxiliar: Gerar estilo de fundo
     */
    private function getEstiloFundo($corFundo)
    {
        if (empty($corFundo)) {
            return 'background: linear-gradient(135deg, #0b1a33 0%, #1a3a5c 100%);';
        }

        $corFundo = trim($corFundo);

        if (str_starts_with($corFundo, '#')) {
            return "background-color: {$corFundo};";
        }

        if (str_contains($corFundo, 'gradient')) {
            return "background: {$corFundo};";
        }

        return "background: {$corFundo};";
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
    // MÉTODOS DE CACHE
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
            Cache::forget('banners_ativos');
            Cache::forget('banners');
            Cache::forget('banners_active');
            
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
            Artisan::call('view:clear');
            Artisan::call('config:clear');
            Artisan::call('route:clear');
            Artisan::call('event:clear');
            Artisan::call('cache:clear');
            Artisan::call('optimize:clear');
            
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