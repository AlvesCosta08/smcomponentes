<?php

namespace Database\Factories;

use App\Models\WishlistItem;
use App\Models\Wishlist;
use App\Models\Produto;
use Illuminate\Database\Eloquent\Factories\Factory;

class WishlistItemFactory extends Factory
{
    protected $model = WishlistItem::class;

    public function definition(): array
    {
        return [
            'wishlist_id' => Wishlist::factory(),
            'produto_id' => Produto::factory(),
            'observacao' => $this->faker->optional()->sentence(),
            'added_at' => now(),
            'created_at' => now(),
            'updated_at' => now()
        ];
    }

    /**
     * Define uma wishlist específica para o item.
     */
    public function emWishlist(Wishlist $wishlist): Factory
    {
        return $this->state(function (array $attributes) use ($wishlist) {
            return ['wishlist_id' => $wishlist->id];
        });
    }

    /**
     * Define um produto específico para o item.
     */
    public function comProduto(Produto $produto): Factory
    {
        return $this->state(function (array $attributes) use ($produto) {
            return ['produto_id' => $produto->id];
        });
    }

    /**
     * Define uma observação para o item.
     */
    public function comObservacao(string $observacao): Factory
    {
        return $this->state(function (array $attributes) use ($observacao) {
            return ['observacao' => $observacao];
        });
    }

    /**
     * Define que o item foi adicionado há X dias.
     */
    public function adicionadoHa(int $dias): Factory
    {
        return $this->state(function (array $attributes) use ($dias) {
            return [
                'added_at' => now()->subDays($dias),
                'created_at' => now()->subDays($dias),
            ];
        });
    }
}