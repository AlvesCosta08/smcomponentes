<?php

namespace App\Http\Controllers;

use App\Models\Banner;
use App\Models\Produto;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Collection;
use stdClass;

class HomeController extends Controller
{
    private const CACHE_TTL = 21600; // 6 horas

    public function index(Request $request): \Illuminate\View\View
    {
        $banners = $this->getBanners();

        $produtosDestaque = $this->getProdutosPaginated(
            'produtos_destaque',
            fn() => $this->getProdutosComSafe('emDestaque'),
            $request->get('page_destaque', 1),
            8,
            'page_destaque'
        );

        $ofertas = $this->getProdutosPaginated(
            'ofertas_ativas',
            fn() => $this->getProdutosComSafe('ofertas'),
            $request->get('page_ofertas', 1),
            8,
            'page_ofertas'
        );

        $novosProdutos = $this->getProdutosPaginated(
            'novos_produtos',
            fn() => $this->getProdutosComSafe('novos'),
            $request->get('page_novos', 1),
            8,
            'page_novos'
        );

        $maisVendidos = $this->getProdutosPaginated(
            'mais_vendidos',
            fn() => $this->getProdutosComSafe('maisVendidos'),
            $request->get('page_vendidos', 1),
            8,
            'page_vendidos'
        );

        $produtosDisponiveis = $this->getProdutosPaginated(
            'produtos_disponiveis',
            fn() => $this->getProdutosComSafe('disponivel'),
            $request->get('page_todos', 1),
            12,
            'page_todos'
        );

        return view('home', compact(
            'banners',
            'produtosDisponiveis',
            'produtosDestaque',
            'ofertas',
            'novosProdutos',
            'maisVendidos'
        ));
    }

    // ================================================================
    // MÉTODOS PRIVADOS
    // ================================================================

    private function getProdutosComSafe(string $scope): Collection
    {
        if (!Schema::hasTable('produtos')) {
            return collect();
        }

        try {
            $query = Produto::query();
            $method = $scope;
            if (method_exists(Produto::class, 'scope' . ucfirst($scope))) {
                $result = $query->$method()->get();
                return $result instanceof Collection ? $result : collect($result);
            }
            return Produto::where('ativo', true)->get();
        } catch (\Exception $e) {
            Log::error("Erro ao buscar produtos com scope '{$scope}': " . $e->getMessage());
            return collect();
        }
    }

    /**
     * Obtém os banners ativos com fallback seguro.
     * Cache armazena apenas arrays para evitar __PHP_Incomplete_Class.
     */
    private function getBanners(): Collection
    {
        if (!Schema::hasTable('banners')) {
            return $this->getDefaultBanner();
        }

        $cachedData = Cache::get('home_banners_data');

        // Se o cache existe mas não é um array válido, remova-o
        if ($cachedData !== null && !is_array($cachedData)) {
            Cache::forget('home_banners_data');
            Log::warning('Cache de banners removido por estar corrompido.');
            $cachedData = null;
        }

        // Se o cache é um array válido, use-o
        if ($cachedData !== null && is_array($cachedData) && !empty($cachedData)) {
            return $this->buildBannerCollection($cachedData);
        }

        try {
            $bannersFromDb = Banner::ativo()->ordenado()->get();

            if ($bannersFromDb->isEmpty()) {
                $default = $this->getDefaultBanner();
                Cache::put('home_banners_data', $default->toArray(), self::CACHE_TTL);
                return $default;
            }

            // Converte para array antes de cachear
            $bannerData = $bannersFromDb->map(fn($banner) => [
                'id'          => $banner->id,
                'titulo'      => $banner->titulo,
                'subtitulo'   => $banner->subtitulo,
                'descricao'   => $banner->descricao,
                'imagem_url'  => $banner->imagem_url,
                'link'        => $banner->link,
                'texto_botao' => $banner->texto_botao,
                'cor_texto'   => $banner->cor_texto,
                'cor_botao'   => $banner->cor_botao,
                'estilo_fundo'=> $banner->estilo_fundo,
            ])->toArray();

            Cache::put('home_banners_data', $bannerData, self::CACHE_TTL);
            return $this->buildBannerCollection($bannerData);
        } catch (\Exception $e) {
            Log::error('Erro ao buscar banners: ' . $e->getMessage());
            return $this->getDefaultBanner();
        }
    }

    /**
     * Constrói uma coleção de banners a partir de um array de dados.
     * Suporta tanto arrays quanto objetos como entrada (evita __PHP_Incomplete_Class).
     */
    private function buildBannerCollection(array $data): Collection
    {
        // Garantia extra de que $data é um array
        if (!is_array($data)) {
            Log::warning('buildBannerCollection recebeu dados não-array, usando fallback.');
            return $this->getDefaultBanner();
        }

        return collect($data)->map(function ($item) {
            // Se for um objeto, converte para array
            if (is_object($item)) {
                // Verifica se é um objeto incompleto e tenta normalizar
                if ($item instanceof __PHP_Incomplete_Class) {
                    Log::warning('Objeto incompleto detectado em banners, usando fallback');
                    return $this->getDefaultBanner()->first();
                }
                // Converte objeto para array
                $item = (array) $item;
            }

            // Se não for array, usa fallback
            if (!is_array($item)) {
                return $this->getDefaultBanner()->first();
            }

            $banner = new stdClass();
            $banner->id          = $item['id'] ?? null;
            $banner->titulo      = $item['titulo'] ?? 'SM Componentes';
            $banner->subtitulo   = $item['subtitulo'] ?? 'Qualidade em Componentes Eletrônicos';
            $banner->descricao   = $item['descricao'] ?? 'Encontre os melhores componentes para seus projetos';
            $banner->imagem_url  = $item['imagem_url'] ?? null;
            $banner->link        = $item['link'] ?? route('produtos.index');
            $banner->texto_botao = $item['texto_botao'] ?? 'Ver Produtos';
            $banner->cor_texto   = $item['cor_texto'] ?? '#ffffff';
            $banner->cor_botao   = $item['cor_botao'] ?? 'primary';
            $banner->estilo_fundo= $item['estilo_fundo'] ?? 'background: linear-gradient(135deg, #0b1a33 0%, #1a3a5c 100%);';
            return $banner;
        });
    }

    /**
     * Retorna o banner padrão.
     */
    private function getDefaultBanner(): Collection
    {
        $banner = new stdClass();
        $banner->id          = null;
        $banner->titulo      = 'SM Componentes';
        $banner->subtitulo   = 'Qualidade em Componentes Eletrônicos';
        $banner->descricao   = 'Encontre os melhores componentes para seus projetos';
        $banner->imagem_url  = null;
        $banner->link        = route('produtos.index');
        $banner->texto_botao = 'Ver Produtos';
        $banner->cor_texto   = '#ffffff';
        $banner->cor_botao   = 'light';
        $banner->estilo_fundo= 'background: linear-gradient(135deg, #0b1a33 0%, #1a3a5c 100%);';

        return collect([$banner]);
    }

    /**
     * Obtém produtos paginados com cache.
     * Cache armazena apenas arrays para evitar __PHP_Incomplete_Class.
     */
    private function getProdutosPaginated(
        string $cacheKey,
        callable $queryBuilder,
        int $page,
        int $perPage = 12,
        string $pageName = 'page'
    ): LengthAwarePaginator {
        if (!Schema::hasTable('produtos')) {
            return new LengthAwarePaginator([], 0, $perPage, $page, [
                'path' => request()->url(),
                'query' => request()->query(),
                'pageName' => $pageName,
            ]);
        }

        $fullCacheKey = "{$cacheKey}_{$pageName}_{$page}";

        // Tenta obter do cache (dados como array)
        $cachedData = Cache::get($fullCacheKey);

        if ($cachedData !== null && is_array($cachedData) && isset($cachedData['items'], $cachedData['total'])) {
            // Reconstrói objetos stdClass a partir dos arrays
            $items = array_map(function ($item) {
                if (is_array($item)) {
                    return (object) $item;
                }
                if (is_object($item) && !($item instanceof __PHP_Incomplete_Class)) {
                    return $item;
                }
                // Se for incompleto, usa fallback
                Log::warning('Item incompleto no cache de produtos', ['key' => $fullCacheKey]);
                return new stdClass();
            }, $cachedData['items']);

            return new LengthAwarePaginator(
                $items,
                $cachedData['total'],
                $perPage,
                $page,
                [
                    'path' => request()->url(),
                    'query' => request()->query(),
                    'pageName' => $pageName,
                ]
            );
        }

        try {
            $items = $queryBuilder();

            if ($items instanceof Collection) {
                $items = $items->all();
            } elseif (is_object($items) && method_exists($items, 'get')) {
                $items = $items->get()->all();
            }

            if (!is_array($items)) {
                $items = [];
            }

            $total = count($items);
            $offset = ($page - 1) * $perPage;
            $paginatedItems = array_slice($items, $offset, $perPage);

            // Prepara dados para cache (serializável)
            $cacheItems = array_map(function ($item) {
                if (is_object($item) && method_exists($item, 'toArray')) {
                    return $item->toArray();
                }
                if (is_object($item)) {
                    return (array) $item;
                }
                return $item;
            }, $paginatedItems);

            $cacheData = [
                'items' => $cacheItems,
                'total' => $total,
            ];

            Cache::put($fullCacheKey, $cacheData, self::CACHE_TTL);

            return new LengthAwarePaginator(
                $paginatedItems,
                $total,
                $perPage,
                $page,
                [
                    'path' => request()->url(),
                    'query' => request()->query(),
                    'pageName' => $pageName,
                ]
            );
        } catch (\Exception $e) {
            Log::error("Erro ao obter produtos paginados para '{$cacheKey}': " . $e->getMessage());
            return new LengthAwarePaginator([], 0, $perPage, $page, [
                'path' => request()->url(),
                'query' => request()->query(),
                'pageName' => $pageName,
            ]);
        }
    }

    // ================================================================
    // MÉTODOS PARA LIMPEZA DE CACHE
    // ================================================================

    public function clearCache(): \Illuminate\Http\RedirectResponse
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
                'email' => auth()->user()->email ?? 'desconhecido'
            ]);

            return redirect()->back()->with('success', '✅ Cache limpo com sucesso!');
        } catch (\Exception $e) {
            Log::error('Erro ao limpar cache', [
                'erro' => $e->getMessage(),
                'usuario_id' => auth()->id()
            ]);

            return redirect()->back()->with('error', '❌ Erro ao limpar cache: ' . $e->getMessage());
        }
    }

    public function clearBannerCache(): \Illuminate\Http\RedirectResponse
    {
        try {
            Cache::forget('home_banners_data');
            Cache::forget('home_banners');
            Cache::forget('banners_ativos');
            Cache::forget('banners');

            Log::info('Cache de banners limpo', [
                'usuario_id' => auth()->id()
            ]);

            return redirect()->back()->with('success', '✅ Cache de banners limpo com sucesso!');
        } catch (\Exception $e) {
            Log::error('Erro ao limpar cache de banners', [
                'erro' => $e->getMessage(),
                'usuario_id' => auth()->id()
            ]);

            return redirect()->back()->with('error', '❌ Erro ao limpar cache de banners: ' . $e->getMessage());
        }
    }

    public function reloadBanners(): \Illuminate\Http\RedirectResponse
    {
        try {
            Cache::forget('home_banners_data');
            Cache::forget('home_banners');
            Cache::forget('banners_ativos');
            Cache::forget('banners');

            if (!Schema::hasTable('banners')) {
                return redirect()->back()->with('warning', '⚠️ Tabela banners não existe!');
            }

            $banners = Banner::ativo()->ordenado()->get();
            $bannerData = $banners->map(fn($banner) => [
                'id'          => $banner->id,
                'titulo'      => $banner->titulo,
                'subtitulo'   => $banner->subtitulo,
                'descricao'   => $banner->descricao,
                'imagem_url'  => $banner->imagem_url,
                'link'        => $banner->link,
                'texto_botao' => $banner->texto_botao,
                'cor_texto'   => $banner->cor_texto,
                'cor_botao'   => $banner->cor_botao,
                'estilo_fundo'=> $banner->estilo_fundo,
            ])->toArray();

            Cache::put('home_banners_data', $bannerData, self::CACHE_TTL);

            Log::info('Banners recarregados', [
                'usuario_id' => auth()->id(),
                'quantidade' => count($bannerData)
            ]);

            return redirect()->back()->with('success', "✅ Banners recarregados com sucesso! (" . count($bannerData) . " banners)");
        } catch (\Exception $e) {
            Log::error('Erro ao recarregar banners', [
                'erro' => $e->getMessage(),
                'usuario_id' => auth()->id()
            ]);

            return redirect()->back()->with('error', '❌ Erro ao recarregar banners: ' . $e->getMessage());
        }
    }

    public function clearProductCache(): \Illuminate\Http\RedirectResponse
    {
        try {
            $keys = [
                'produtos_destaque',
                'ofertas_ativas',
                'novos_produtos',
                'mais_vendidos',
                'produtos_disponiveis'
            ];

            foreach ($keys as $key) {
                Cache::forget($key);
                for ($i = 1; $i <= 10; $i++) {
                    Cache::forget($key . '_page_destaque_' . $i);
                    Cache::forget($key . '_page_ofertas_' . $i);
                    Cache::forget($key . '_page_novos_' . $i);
                    Cache::forget($key . '_page_vendidos_' . $i);
                    Cache::forget($key . '_page_todos_' . $i);
                }
            }

            Log::info('Cache de produtos limpo', [
                'usuario_id' => auth()->id()
            ]);

            return redirect()->back()->with('success', '✅ Cache de produtos limpo com sucesso!');
        } catch (\Exception $e) {
            Log::error('Erro ao limpar cache de produtos', [
                'erro' => $e->getMessage(),
                'usuario_id' => auth()->id()
            ]);

            return redirect()->back()->with('error', '❌ Erro ao limpar cache de produtos: ' . $e->getMessage());
        }
    }

    public function clearAllCache(): \Illuminate\Http\RedirectResponse
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
            Log::error('Erro ao limpar todos os caches', [
                'erro' => $e->getMessage(),
                'usuario_id' => auth()->id()
            ]);

            return redirect()->back()->with('error', '❌ Erro ao limpar caches: ' . $e->getMessage());
        }
    }

    // ================================================================
    // PÁGINAS ESTÁTICAS
    // ================================================================

    public function termos(): \Illuminate\View\View
    {
        return view('pages.termos');
    }

    public function privacidade(): \Illuminate\View\View
    {
        return view('pages.privacidade');
    }

    public function contato(): \Illuminate\View\View
    {
        return view('pages.contato');
    }

    public function sobre(): \Illuminate\View\View
    {
        return view('pages.sobre');
    }

    public function faq(): \Illuminate\View\View
    {
        return view('pages.faq');
    }
}