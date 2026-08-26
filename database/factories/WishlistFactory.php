<?php

namespace Database\Factories;

use App\Models\Wishlist;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class WishlistFactory extends Factory
{
    protected $model = Wishlist::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'nome' => $this->faker->words(2, true),
            'created_at' => now(),
            'updated_at' => now()
        ];
    }

    public function comNome(string $nome): Factory
    {
        return $this->state(function (array $attributes) use ($nome) {
            return ['nome' => $nome];
        });
    }
}