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
use Illuminate\Support\Collection;
use stdClass;

class HomeController extends Controller
{
    /**
     * Tempo de cache em segundos (1 hora)
     */
    private const CACHE_TTL = 3600;

    /**
     * Exibe a página inicial.
     */
    public function index(Request $request): \Illuminate\View\View
    {
        $banners = $this->getBanners();
        
        $produtosDestaque = $this->getProdutosPaginated(
            'produtos_destaque',
            fn() => Produto::emDestaque()->get(),
            $request->get('page_destaque', 1),
            8,
            'page_destaque'
        );
        
        $ofertas = $this->getProdutosPaginated(
            'ofertas_ativas',
            fn() => Produto::ofertas()->get(),
            $request->get('page_ofertas', 1),
            8,
            'page_ofertas'
        );
        
        $novosProdutos = $this->getProdutosPaginated(
            'novos_produtos',
            fn() => Produto::novos()->get(),
            $request->get('page_novos', 1),
            8,
            'page_novos'
        );
        
        $maisVendidos = $this->getProdutosPaginated(
            'mais_vendidos',
            fn() => Produto::maisVendidos()->get(),
            $request->get('page_vendidos', 1),
            8,
            'page_vendidos'
        );
        
        $produtosDisponiveis = $this->getProdutosPaginated(
            'produtos_disponiveis',
            fn() => Produto::disponivel()->get(),
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

    /**
     * Obtém os banners ativos com fallback.
     */
    private function getBanners(): Collection
    {
        return Cache::remember('home_banners', self::CACHE_TTL, function () {
            $bannersFromDb = Banner::ativo()->ordenado()->get();

            if ($bannersFromDb->isEmpty()) {
                return $this->getDefaultBanner();
            }

            return $bannersFromDb->map(fn($banner) => $this->formatBanner($banner));
        });
    }

    /**
     * Retorna o banner padrão.
     */
    private function getDefaultBanner(): Collection
    {
        $banner = new stdClass();
        $banner->id = null;
        $banner->titulo = 'SM Componentes';
        $banner->subtitulo = 'Qualidade em Componentes Eletrônicos';
        $banner->descricao = 'Encontre os melhores componentes para seus projetos';
        $banner->imagem_url = null;
        $banner->link = route('produtos.index');
        $banner->texto_botao = 'Ver Produtos';
        $banner->cor_texto = '#ffffff';
        $banner->cor_botao = 'light';
        $banner->estilo_fundo = 'background: linear-gradient(135deg, #0b1a33 0%, #1a3a5c 100%);';

        return collect([$banner]);
    }

    /**
     * Formata um banner para exibição.
     */
    private function formatBanner(Banner $banner): stdClass
    {
        $obj = new stdClass();
        $obj->id = $banner->id;
        
        // Textos
        $obj->titulo = $banner->titulo ?? 'SM Componentes';
        $obj->subtitulo = $banner->subtitulo ?? 'Qualidade em Componentes Eletrônicos';
        $obj->descricao = $banner->descricao ?? 'Encontre os melhores componentes para seus projetos';
        
        // Imagem - USANDO O ACCESSOR DO MODEL
        $obj->imagem_url = $banner->imagem_url;
        
        // Link e botão
        $obj->link = $banner->link ?? route('produtos.index');
        $obj->texto_botao = $banner->texto_botao ?? 'Ver Produtos';
        
        // Cores
        $obj->cor_texto = $banner->cor_texto ?? '#ffffff';
        $obj->cor_botao = $banner->cor_botao ?? 'primary';
        
        // Estilo de fundo - USANDO O ACCESSOR DO MODEL
        $obj->estilo_fundo = $banner->estilo_fundo;

        return $obj;
    }

    /**
     * Obtém produtos paginados com cache
     */
    private function getProdutosPaginated(
        string $cacheKey,
        callable $queryBuilder,
        int $page,
        int $perPage = 12,
        string $pageName = 'page'
    ): LengthAwarePaginator {
        $fullCacheKey = "{$cacheKey}_{$pageName}_{$page}";

        return Cache::remember($fullCacheKey, self::CACHE_TTL, function () use ($queryBuilder, $perPage, $page, $pageName) {
            $items = $queryBuilder();
            
            if ($items instanceof Collection) {
                $items = $items->all();
            }
            
            if (is_object($items) && method_exists($items, 'get')) {
                $items = $items->get()->all();
            }
            
            if (!is_array($items)) {
                $items = [];
            }

            $total = count($items);
            $offset = ($page - 1) * $perPage;
            $items = array_slice($items, $offset, $perPage);
            
            return new LengthAwarePaginator(
                $items,
                $total,
                $perPage,
                $page,
                [
                    'path' => request()->url(),
                    'query' => request()->query(),
                    'pageName' => $pageName,
                ]
            );
        });
    }

    // ================================================================
    // MÉTODOS DE CACHE
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
            Cache::forget('home_banners');
            Cache::forget('banners_ativos');
            Cache::forget('banners');
            Cache::forget('banners_active');

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
            Cache::forget('home_banners');
            Cache::forget('banners_ativos');
            Cache::forget('banners');
            Cache::forget('banners_active');

            $banners = Banner::ativo()->ordenado()->get();
            Cache::put('home_banners', $banners, self::CACHE_TTL);
            Cache::put('banners_ativos', $banners, self::CACHE_TTL);

            Log::info('Banners recarregados', [
                'usuario_id' => auth()->id(),
                'quantidade' => $banners->count()
            ]);

            return redirect()->back()->with('success', "✅ Banners recarregados com sucesso! ({$banners->count()} banners)");
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