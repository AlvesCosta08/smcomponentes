<?php
// app/Services/WishlistService.php

namespace App\Services;

use App\DTOs\WishlistDTO;
use App\Models\Produto;
use App\Models\Wishlist;
use App\Models\WishlistItem;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class WishlistService
{
    /**
     * Obter wishlist do usuário (ou criar padrão)
     */
    public function getOrCreateDefaultWishlist(int $userId): Wishlist
    {
        $wishlist = Wishlist::where('user_id', $userId)
            ->where('is_default', true)
            ->first();

        if (!$wishlist) {
            $wishlist = Wishlist::create([
                'user_id' => $userId,
                'nome' => 'Minha Lista de Desejos',
                'is_default' => true,
                'is_public' => false,
            ]);
        }

        return $wishlist;
    }

    /**
     * Obter todas as wishlists do usuário
     */
    public function getUserWishlists(int $userId): Collection
    {
        return Wishlist::where('user_id', $userId)
            ->with(['items.produto'])
            ->orderBy('is_default', 'desc')
            ->orderBy('nome')
            ->get();
    }

    /**
     * Obter wishlist por ID
     */
    public function getWishlist(int $id): ?Wishlist
    {
        return Wishlist::with(['items.produto'])->find($id);
    }

    /**
     * Criar nova wishlist
     */
    public function createWishlist(WishlistDTO $dto): Wishlist
    {
        try {
            DB::beginTransaction();

            $wishlist = Wishlist::create($dto->toArray());

            DB::commit();

            Log::info('Wishlist criada', [
                'user_id' => $dto->user_id,
                'wishlist_id' => $wishlist->id,
            ]);

            return $wishlist;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao criar wishlist: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Atualizar wishlist
     */
    public function updateWishlist(int $id, array $data): Wishlist
    {
        try {
            DB::beginTransaction();

            $wishlist = Wishlist::findOrFail($id);
            $wishlist->update($data);

            DB::commit();

            Log::info('Wishlist atualizada', [
                'wishlist_id' => $id,
            ]);

            return $wishlist;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao atualizar wishlist: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Deletar wishlist
     */
    public function deleteWishlist(int $id): bool
    {
        try {
            DB::beginTransaction();

            $wishlist = Wishlist::findOrFail($id);
            
            // Não permitir deletar a wishlist padrão
            if ($wishlist->is_default) {
                throw new \Exception('Não é possível deletar a wishlist padrão.');
            }

            $deleted = $wishlist->delete();

            DB::commit();

            Log::info('Wishlist deletada', [
                'wishlist_id' => $id,
            ]);

            return $deleted;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao deletar wishlist: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Adicionar produto à wishlist
     */
    public function addProductToWishlist(int $wishlistId, int $produtoId, ?string $observacao = null): bool
    {
        try {
            DB::beginTransaction();

            $wishlist = Wishlist::findOrFail($wishlistId);
            $produto = Produto::findOrFail($produtoId);

            // Verificar se já existe
            if ($wishlist->hasProduct($produtoId)) {
                throw new \Exception('Produto já está na lista de desejos.');
            }

            $wishlist->addProduct($produto, $observacao);

            DB::commit();

            Log::info('Produto adicionado à wishlist', [
                'wishlist_id' => $wishlistId,
                'produto_id' => $produtoId,
            ]);

            return true;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao adicionar produto à wishlist: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Remover produto da wishlist
     */
    public function removeProductFromWishlist(int $wishlistId, int $produtoId): bool
    {
        try {
            DB::beginTransaction();

            $wishlist = Wishlist::findOrFail($wishlistId);
            $produto = Produto::findOrFail($produtoId);

            $removed = $wishlist->removeProduct($produto);

            DB::commit();

            Log::info('Produto removido da wishlist', [
                'wishlist_id' => $wishlistId,
                'produto_id' => $produtoId,
            ]);

            return $removed;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao remover produto da wishlist: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Adicionar produto à wishlist padrão do usuário
     */
    public function addToDefaultWishlist(int $userId, int $produtoId): bool
    {
        $wishlist = $this->getOrCreateDefaultWishlist($userId);
        return $this->addProductToWishlist($wishlist->id, $produtoId);
    }

    /**
     * Remover produto da wishlist padrão do usuário
     */
    public function removeFromDefaultWishlist(int $userId, int $produtoId): bool
    {
        $wishlist = $this->getOrCreateDefaultWishlist($userId);
        return $this->removeProductFromWishlist($wishlist->id, $produtoId);
    }

    /**
     * Verificar se produto está na wishlist do usuário
     */
    public function isInWishlist(int $userId, int $produtoId): bool
    {
        $wishlist = $this->getOrCreateDefaultWishlist($userId);
        return $wishlist->hasProduct($produtoId);
    }
}