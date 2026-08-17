<?php

namespace App\Providers;

use App\Repositories\PedidoRepository;
use App\Repositories\ProdutoRepository;
use App\Services\BannerService;
use App\Services\DashboardService;
use App\Services\OrderAdminService;
use App\Services\OrderService;
use App\Services\PaymentService;
use App\Services\ProductService;
use App\Services\StockService;
use App\Services\UserService;
use App\Services\WishlistService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Str;
use Carbon\Carbon;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Log apenas em desenvolvimento/não produção
        if (!app()->isProduction()) {
            Log::info('🚀 AppServiceProvider::register() EXECUTADO!');
        }

        try {
            // ============================================
            // REPOSITORIES
            // ============================================
            
            // Repositories geralmente são stateless, podem ser singletons
            $this->app->singleton(ProdutoRepository::class);
            $this->app->singleton(PedidoRepository::class);

            // ============================================
            // SERVICES
            // ============================================
            
            // ✅ Usar singleton APENAS para serviços com estado
            // ou que são caros de instanciar
            
            // StockService - Stateless, pode ser bind normal
            $this->app->bind(StockService::class);
            
            // ProductService - Depende de repositórios
            $this->app->singleton(ProductService::class, function ($app) {
                return new ProductService(
                    $app->make(ProdutoRepository::class),
                    $app->make(StockService::class)
                );
            });

            // OrderService - Depende de repositórios
            $this->app->singleton(OrderService::class, function ($app) {
                return new OrderService(
                    $app->make(PedidoRepository::class),
                    $app->make(StockService::class)
                );
            });

            // OrderAdminService - Depende de repositórios
            $this->app->singleton(OrderAdminService::class, function ($app) {
                return new OrderAdminService(
                    $app->make(PedidoRepository::class),
                    $app->make(StockService::class)
                );
            });

            // 🆕 Services que NÃO precisam de singleton (stateless)
            $this->app->bind(DashboardService::class);
            $this->app->bind(UserService::class);
            $this->app->bind(BannerService::class);
            $this->app->bind(PaymentService::class);
            $this->app->bind(WishlistService::class);

            // Log apenas em desenvolvimento
            if (!app()->isProduction()) {
                Log::info('✅ Todos os services registrados com sucesso!');
            }
            
        } catch (\Exception $e) {
            // Log de erro SEMPRE (mesmo em produção)
            Log::error('❌ ERRO no AppServiceProvider: ' . $e->getMessage());
            Log::error($e->getTraceAsString());
            throw $e;
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Log apenas em desenvolvimento
        if (!app()->isProduction()) {
            Log::info('🚀 AppServiceProvider::boot() EXECUTADO!');
        }

        // ============================================
        // SEGURANÇA
        // ============================================
        
        // ✅ Forçar HTTPS em produção
        if (app()->isProduction()) {
            URL::forceScheme('https');
        }

        // ============================================
        // CONFIGURAÇÕES DE DATA/HORA
        // ============================================
        
        // ✅ Configurar timezone (já definido no config/app.php)
        date_default_timezone_set(config('app.timezone', 'America/Sao_Paulo'));

        // ============================================
        // PAGINAÇÃO
        // ============================================
        
        // Configurar paginação com Bootstrap 5
        Paginator::useBootstrapFive();

        // ============================================
        // MACROS - Otimizadas
        // ============================================
        
        $this->registerMacros();

        // ============================================
        // CONFIGURAÇÃO DO SPATIE PERMISSION
        // ============================================
        
        $this->configureSpatiePermission();
    }

    /**
     * Register all String macros
     */
    protected function registerMacros(): void
    {
        // ✅ Verificação mais robusta para evitar re-registro
        if (!Str::hasMacro('currency')) {
            Str::macro('currency', fn($value) => 
                'R$ ' . number_format($value, 2, ',', '.')
            );
        }

        if (!Str::hasMacro('cpf')) {
            Str::macro('cpf', function ($value) {
                $value = preg_replace('/[^0-9]/', '', $value);
                if (strlen($value) === 11) {
                    return substr($value, 0, 3) . '.' . 
                           substr($value, 3, 3) . '.' . 
                           substr($value, 6, 3) . '-' . 
                           substr($value, 9, 2);
                }
                return $value;
            });
        }

        if (!Str::hasMacro('phone')) {
            Str::macro('phone', function ($value) {
                $value = preg_replace('/[^0-9]/', '', $value);
                if (strlen($value) === 11) {
                    return '(' . substr($value, 0, 2) . ') ' . 
                           substr($value, 2, 5) . '-' . 
                           substr($value, 7, 4);
                }
                if (strlen($value) === 10) {
                    return '(' . substr($value, 0, 2) . ') ' . 
                           substr($value, 2, 4) . '-' . 
                           substr($value, 6, 4);
                }
                return $value;
            });
        }

        if (!Str::hasMacro('cep')) {
            Str::macro('cep', function ($value) {
                $value = preg_replace('/[^0-9]/', '', $value);
                if (strlen($value) === 8) {
                    return substr($value, 0, 5) . '-' . substr($value, 5, 3);
                }
                return $value;
            });
        }

        if (!Str::hasMacro('data')) {
            Str::macro('data', fn($value) => 
                $value ? Carbon::parse($value)->format('d/m/Y') : ''
            );
        }

        if (!Str::hasMacro('datahora')) {
            Str::macro('datahora', fn($value) => 
                $value ? Carbon::parse($value)->format('d/m/Y H:i') : ''
            );
        }
    }

    /**
     * Configure Spatie Permission
     */
    protected function configureSpatiePermission(): void
    {
        try {
            // ✅ Apenas tenta carregar se a classe existir
            if (class_exists(\Spatie\Permission\PermissionRegistrar::class)) {
                $this->app->make(\Spatie\Permission\PermissionRegistrar::class);
            }
        } catch (\Exception $e) {
            // Em produção, log mais silencioso
            if (!app()->isProduction()) {
                Log::warning('Erro ao carregar PermissionRegistrar: ' . $e->getMessage());
            }
        }
    }
}