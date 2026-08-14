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

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        Log::info('🚀 AppServiceProvider::register() EXECUTADO!');
        
        try {
            // ============================================
            // REPOSITORIES
            // ============================================
            
            $this->app->singleton(ProdutoRepository::class, function ($app) {
                Log::info('✅ ProdutoRepository registrado!');
                return new ProdutoRepository();
            });

            $this->app->singleton(PedidoRepository::class, function ($app) {
                Log::info('✅ PedidoRepository registrado!');
                return new PedidoRepository();
            });

            // ============================================
            // SERVICES
            // ============================================
            
            // StockService
            $this->app->singleton(StockService::class, function ($app) {
                Log::info('✅ StockService registrado!');
                return new StockService();
            });

            // ProductService
            $this->app->singleton(ProductService::class, function ($app) {
                Log::info('✅ ProductService registrado!');
                return new ProductService(
                    $app->make(ProdutoRepository::class),
                    $app->make(StockService::class)
                );
            });

            // OrderService (para clientes)
            $this->app->singleton(OrderService::class, function ($app) {
                Log::info('✅ OrderService registrado!');
                return new OrderService(
                    $app->make(PedidoRepository::class),
                    $app->make(StockService::class)
                );
            });

            // OrderAdminService (para admin)
            $this->app->singleton(OrderAdminService::class, function ($app) {
                Log::info('✅ OrderAdminService registrado!');
                return new OrderAdminService(
                    $app->make(PedidoRepository::class),
                    $app->make(StockService::class)
                );
            });

            // DashboardService
            $this->app->singleton(DashboardService::class, function ($app) {
                Log::info('✅ DashboardService registrado!');
                return new DashboardService();
            });

            // UserService
            $this->app->singleton(UserService::class, function ($app) {
                Log::info('✅ UserService registrado!');
                return new UserService();
            });

            // BannerService
            $this->app->singleton(BannerService::class, function ($app) {
                Log::info('✅ BannerService registrado!');
                return new BannerService();
            });

            // PaymentService (Mercado Pago)
            $this->app->singleton(PaymentService::class, function ($app) {
                Log::info('✅ PaymentService registrado!');
                return new PaymentService();
            });

            // 🆕 WishlistService
            $this->app->singleton(WishlistService::class, function ($app) {
                Log::info('✅ WishlistService registrado!');
                return new WishlistService();
            });

            Log::info('✅ Todos os services registrados com sucesso!');
            
        } catch (\Exception $e) {
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
        Log::info('🚀 AppServiceProvider::boot() EXECUTADO!');
        
        // ============================================
        // SEGURANÇA
        // ============================================
        
        // Forçar HTTPS em produção
        if (config('app.env') === 'production') {
            URL::forceScheme('https');
        }

        // ============================================
        // CONFIGURAÇÕES DE DATA/HORA
        // ============================================
        
        // Configurar timezone
        date_default_timezone_set(config('app.timezone', 'America/Sao_Paulo'));

        // ============================================
        // PAGINAÇÃO
        // ============================================
        
        // Configurar paginação com Bootstrap 5
        \Illuminate\Pagination\Paginator::useBootstrapFive();

        // ============================================
        // MACROS
        // ============================================
        
        // Macro para formatação de moeda
        if (!\Illuminate\Support\Str::hasMacro('currency')) {
            \Illuminate\Support\Str::macro('currency', function ($value) {
                return 'R$ ' . number_format($value, 2, ',', '.');
            });
        }

        // Macro para formatação de CPF
        if (!\Illuminate\Support\Str::hasMacro('cpf')) {
            \Illuminate\Support\Str::macro('cpf', function ($value) {
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

        // Macro para formatação de telefone
        if (!\Illuminate\Support\Str::hasMacro('phone')) {
            \Illuminate\Support\Str::macro('phone', function ($value) {
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

        // Macro para formatação de CEP
        if (!\Illuminate\Support\Str::hasMacro('cep')) {
            \Illuminate\Support\Str::macro('cep', function ($value) {
                $value = preg_replace('/[^0-9]/', '', $value);
                if (strlen($value) === 8) {
                    return substr($value, 0, 5) . '-' . substr($value, 5, 3);
                }
                return $value;
            });
        }

        // Macro para formatação de data
        if (!\Illuminate\Support\Str::hasMacro('data')) {
            \Illuminate\Support\Str::macro('data', function ($value) {
                if (!$value) return '';
                $date = \Carbon\Carbon::parse($value);
                return $date->format('d/m/Y');
            });
        }

        // Macro para formatação de data e hora
        if (!\Illuminate\Support\Str::hasMacro('datahora')) {
            \Illuminate\Support\Str::macro('datahora', function ($value) {
                if (!$value) return '';
                $date = \Carbon\Carbon::parse($value);
                return $date->format('d/m/Y H:i');
            });
        }

        // ============================================
        // CONFIGURAÇÃO DO SPATIE PERMISSION
        // ============================================
        
        try {
            $this->app->make(\Spatie\Permission\PermissionRegistrar::class);
        } catch (\Exception $e) {
            Log::warning('Erro ao carregar PermissionRegistrar: ' . $e->getMessage());
        }
    }
}