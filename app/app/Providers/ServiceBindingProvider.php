<?php
// app/Providers/ServiceBindingProvider.php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

// ============================================
// SERVICES - INTERFACES
// ============================================
use App\Services\Contracts\{
    OrderServiceInterface,
    StockServiceInterface,
    PaymentServiceInterface,
    ProductServiceInterface,
    UserServiceInterface,
    BannerServiceInterface,
    WishlistServiceInterface,
    DashboardServiceInterface,
    CheckoutServiceInterface,
    OrderAdminServiceInterface
};

// ============================================
// SERVICES - IMPLEMENTAÇÕES
// ============================================
use App\Services\{
    OrderService,
    StockService,
    PaymentService,
    ProductService,
    UserService,
    BannerService,
    WishlistService,
    DashboardService,
    CheckoutService,
    OrderAdminService
};

// ============================================
// REPOSITORIES - INTERFACES
// ============================================
use App\Repositories\Contracts\{
    ProdutoRepositoryInterface,
    PedidoRepositoryInterface,
    PedidoItemRepositoryInterface,
    UserRepositoryInterface,
    BannerRepositoryInterface,
    WishlistRepositoryInterface,
    WishlistItemRepositoryInterface
};

// ============================================
// REPOSITORIES - IMPLEMENTAÇÕES
// ============================================
use App\Repositories\{
    ProdutoRepository,
    PedidoRepository,
    PedidoItemRepository,
    UserRepository,
    BannerRepository,
    WishlistRepository,
    WishlistItemRepository
};

class ServiceBindingProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // ============================================
        // BINDINGS DOS SERVICES
        // ============================================
        
        // Bindings principais
        $this->app->bind(OrderServiceInterface::class, OrderService::class);
        $this->app->bind(StockServiceInterface::class, StockService::class);
        $this->app->bind(ProductServiceInterface::class, ProductService::class);
        $this->app->bind(UserServiceInterface::class, UserService::class);
        $this->app->bind(BannerServiceInterface::class, BannerService::class);
        $this->app->bind(WishlistServiceInterface::class, WishlistService::class);
        $this->app->bind(DashboardServiceInterface::class, DashboardService::class);
        $this->app->bind(CheckoutServiceInterface::class, CheckoutService::class);
        $this->app->bind(OrderAdminServiceInterface::class, OrderAdminService::class);

        // Singleton para serviços com estado
        $this->app->singleton(PaymentServiceInterface::class, PaymentService::class);

        // ============================================
        // BINDINGS DOS REPOSITORIES
        // ============================================
        
        // Repositories principais
        $this->app->bind(ProdutoRepositoryInterface::class, ProdutoRepository::class);
        $this->app->bind(PedidoRepositoryInterface::class, PedidoRepository::class);
        $this->app->bind(PedidoItemRepositoryInterface::class, PedidoItemRepository::class);
        $this->app->bind(UserRepositoryInterface::class, UserRepository::class);
        $this->app->bind(BannerRepositoryInterface::class, BannerRepository::class);
        $this->app->bind(WishlistRepositoryInterface::class, WishlistRepository::class);
        $this->app->bind(WishlistItemRepositoryInterface::class, WishlistItemRepository::class);

        // ============================================
        // BINDINGS COM DEPENDÊNCIAS ESPECÍFICAS
        // ============================================
        
        // Se algum Service precisar de dependências específicas
        $this->app->when(OrderService::class)
            ->needs(PedidoRepositoryInterface::class)
            ->give(PedidoRepository::class);

        $this->app->when(OrderService::class)
            ->needs(ProdutoRepositoryInterface::class)
            ->give(ProdutoRepository::class);

        $this->app->when(OrderService::class)
            ->needs(PedidoItemRepositoryInterface::class)
            ->give(PedidoItemRepository::class);

        // ============================================
        // CONTEXTUAL BINDINGS (opcional)
        // ============================================
        
        // Se você precisar de implementações diferentes para contextos diferentes
        // $this->app->when(AdminController::class)
        //     ->needs(ProdutoRepositoryInterface::class)
        //     ->give(ProdutoAdminRepository::class);

        // ============================================
        // ALIASES PARA FACILITAR (opcional)
        // ============================================
        
        // $this->app->alias(OrderServiceInterface::class, 'order.service');
        // $this->app->alias(StockServiceInterface::class, 'stock.service');
        // $this->app->alias(ProdutoRepositoryInterface::class, 'produto.repository');

        // ============================================
        // BINDING DE INTERFACES COM MÚLTIPLAS IMPLEMENTAÇÕES
        // ============================================
        
        // Se você tiver múltiplos gateways de pagamento
        // $this->app->bind(PaymentGatewayInterface::class, function($app) {
        //     return config('payment.default') === 'mercadopago' 
        //         ? new MercadoPagoGateway()
        //         : new PagSeguroGateway();
        // });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }

    /**
     * Get the services provided by the provider.
     */
    public function provides(): array
    {
        return [
            // Services
            OrderServiceInterface::class,
            StockServiceInterface::class,
            PaymentServiceInterface::class,
            ProductServiceInterface::class,
            UserServiceInterface::class,
            BannerServiceInterface::class,
            WishlistServiceInterface::class,
            DashboardServiceInterface::class,
            CheckoutServiceInterface::class,
            OrderAdminServiceInterface::class,
            
            // Repositories
            ProdutoRepositoryInterface::class,
            PedidoRepositoryInterface::class,
            PedidoItemRepositoryInterface::class,
            UserRepositoryInterface::class,
            BannerRepositoryInterface::class,
            WishlistRepositoryInterface::class,
            WishlistItemRepositoryInterface::class,
        ];
    }
}