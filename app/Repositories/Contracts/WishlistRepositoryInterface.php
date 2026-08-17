<?php
// app/Repositories/Contracts/WishlistRepositoryInterface.php

namespace App\Repositories\Contracts;

use App\Models\Wishlist;
use App\Models\WishlistItem;
use Illuminate\Database\Eloquent\Collection;

interface WishlistRepositoryInterface extends RepositoryInterface
{
    /**
     * Buscar wishlist por usuário
     *
     * @param int $userId
     * @return Wishlist|null
     */
    public function findByUser(int $userId): ?Wishlist;

    /**
     * Buscar wishlist com itens e produtos
     *
     * @param int $userId
     * @return Wishlist|null
     */
    public function findWithItems(int $userId): ?Wishlist;

    /**
     * Adicionar item à wishlist
     *
     * @param int $wishlistId
     * @param int $productId
     * @return WishlistItem
     */
    public function addItem(int $wishlistId, int $productId): WishlistItem;

    /**
     * Remover item da wishlist
     *
     * @param int $wishlistId
     * @param int $productId
     * @return bool
     */
    public function removeItem(int $wishlistId, int $productId): bool;

    /**
     * Verificar se produto está na wishlist
     *
     * @param int $wishlistId
     * @param int $productId
     * @return bool
     */
    public function hasProduct(int $wishlistId, int $productId): bool;

    /**
     * Contar itens da wishlist
     *
     * @param int $wishlistId
     * @return int
     */
    public function countItems(int $wishlistId): int;

    /**
     * Limpar wishlist
     *
     * @param int $wishlistId
     * @return bool
     */
    public function clear(int $wishlistId): bool;

    /**
     * Obter produtos da wishlist
     *
     * @param int $wishlistId
     * @return Collection
     */
    public function getProducts(int $wishlistId): Collection;

    /**
     * Mover itens para o carrinho
     *
     * @param int $wishlistId
     * @param array $itemIds
     * @return int
     */
    public function moveToCart(int $wishlistId, array $itemIds): int;

    /**
     * Criar wishlist para usuário se não existir
     *
     * @param int $userId
     * @return Wishlist
     */
    public function getOrCreate(int $userId): Wishlist;

    /**
     * Obter wishlists mais populares
     *
     * @param int $limit
     * @return Collection
     */
    public function getMostPopular(int $limit = 10): Collection;
}