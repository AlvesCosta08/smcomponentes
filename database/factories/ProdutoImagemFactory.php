<?php

namespace Database\Factories;

use App\Models\ProdutoImagem;
use App\Models\Produto;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProdutoImagemFactory extends Factory
{
    protected $model = ProdutoImagem::class;

    public function definition(): array
    {
        return [
            'produto_id' => Produto::factory(),
            'imagem' => $this->faker->imageUrl(640, 480, 'products', true), // ← CORRIGIDO: 'imagem' em vez de 'caminho'
            'principal' => $this->faker->boolean(20),
            'ordem' => $this->faker->numberBetween(0, 10),
            'created_at' => now(),
            'updated_at' => now()
        ];
    }

    public function principal(): Factory
    {
        return $this->state(function (array $attributes) {
            return ['principal' => true];
        });
    }

    public function secundaria(): Factory
    {
        return $this->state(function (array $attributes) {
            return ['principal' => false];
        });
    }

    public function comOrdem(int $ordem): Factory
    {
        return $this->state(function (array $attributes) use ($ordem) {
            return ['ordem' => $ordem];
        });
    }
}