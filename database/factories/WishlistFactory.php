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
            'descricao' => $this->faker->optional()->sentence(),
            'is_default' => false,
            'is_public' => $this->faker->boolean(30),
            'created_at' => now(),
            'updated_at' => now()
        ];
    }

    /**
     * Define a wishlist como padrão.
     */
    public function default(): Factory
    {
        return $this->state(function (array $attributes) {
            return [
                'is_default' => true,
                'nome' => 'Minha Lista',
            ];
        });
    }

    /**
     * Define a wishlist como pública.
     */
    public function publica(): Factory
    {
        return $this->state(function (array $attributes) {
            return [
                'is_public' => true,
            ];
        });
    }

    /**
     * Define a wishlist como privada.
     */
    public function privada(): Factory
    {
        return $this->state(function (array $attributes) {
            return [
                'is_public' => false,
            ];
        });
    }

    /**
     * Define um nome específico para a wishlist.
     */
    public function comNome(string $nome): Factory
    {
        return $this->state(function (array $attributes) use ($nome) {
            return ['nome' => $nome];
        });
    }

    /**
     * Define uma descrição específica para a wishlist.
     */
    public function comDescricao(string $descricao): Factory
    {
        return $this->state(function (array $attributes) use ($descricao) {
            return ['descricao' => $descricao];
        });
    }

    /**
     * Define um usuário específico para a wishlist.
     */
    public function paraUsuario(User $user): Factory
    {
        return $this->state(function (array $attributes) use ($user) {
            return ['user_id' => $user->id];
        });
    }
}