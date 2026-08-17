<?php
// app/Services/Contracts/WishlistServiceInterface.php

namespace App\Services\Contracts;

use App\Models\Wishlist;
use App\Models\Produto;
use App\DTOs\WishlistDTO;
use Illuminate\Database\Eloquent\Collection;

interface WishlistServiceInterface
{
    /**
     * Obter lista de desejos do usuário
     *
     * @param int $userId
     * @return Wishlist|null
     */
    public function getUserWishlist(int $userId): ?Wishlist;

    /**
     * Adicionar produto à lista de desejos
     *
     * @param int $userId
     * @param int $productId
     * @return bool
     */
    public function addToWishlist(int $userId, int $productId): bool;

    /**
     * Remover produto da lista de desejos
     *
     * @param int $userId
     * @param int $productId
     * @return bool
     */
    public function removeFromWishlist(int $userId, int $productId): bool;

    /**
     * Verificar se produto está na lista de desejos
     *
     * @param int $userId
     * @param int $productId
     * @return bool
     */
    public function isInWishlist(int $userId, int $productId): bool;

    /**
     * Obter quantidade de itens na lista de desejos
     *
     * @param int $userId
     * @return int
     */
    public function getWishlistCount(int $userId): int;

    /**
     * Mover itens da lista de desejos para o carrinho
     *
     * @param int $userId
     * @param array $itemIds IDs dos itens a mover
     * @return int Quantidade de itens movidos
     */
    public function moveToCart(int $userId, array $itemIds): int;

    /**
     * Limpar lista de desejos
     *
     * @param int $userId
     * @return bool
     */
    public function clearWishlist(int $userId): bool;

    /**
     * Criar nova lista de desejos (útil para múltiplas listas)
     *
     * @param int $userId
     * @param WishlistDTO $dto
     * @return Wishlist
     */
    public function createWishlist(int $userId, WishlistDTO $dto): Wishlist;

    /**
     * Atualizar lista de desejos
     *
     * @param int $wishlistId
     * @param WishlistDTO $dto
     * @return Wishlist
     */
    public function updateWishlist(int $wishlistId, WishlistDTO $dto): Wishlist;

    /**
     * Excluir lista de desejos
     *
     * @param int $wishlistId
     * @return bool
     */
    public function deleteWishlist(int $wishlistId): bool;

    /**
     * Obter produtos da lista de desejos com detalhes
     *
     * @param int $userId
     * @return Collection
     */
    public function getWishlistProducts(int $userId): Collection;

    /**
     * Notificar produtos em promoção na lista de desejos
     *
     * @param int $userId
     * @return void
     */
    public function notifyWishlistPromotions(int $userId): void;
}