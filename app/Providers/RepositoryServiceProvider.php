<?php
// app/Providers/RepositoryServiceProvider.php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Repositories\Contracts\{
    ProdutoRepositoryInterface,
    PedidoRepositoryInterface,
    UserRepositoryInterface,
    BannerRepositoryInterface,
    WishlistRepositoryInterface,
    WishlistItemRepositoryInterface
};
use App\Repositories\{
    ProdutoRepository,
    PedidoRepository,
    UserRepository,
    BannerRepository,
    WishlistRepository,
    WishlistItemRepository
};

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Bindings dos Repositories
        $this->app->bind(ProdutoRepositoryInterface::class, ProdutoRepository::class);
        $this->app->bind(PedidoRepositoryInterface::class, PedidoRepository::class);
        $this->app->bind(UserRepositoryInterface::class, UserRepository::class);
        $this->app->bind(BannerRepositoryInterface::class, BannerRepository::class);
        $this->app->bind(WishlistRepositoryInterface::class, WishlistRepository::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}