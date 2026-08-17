<?php

namespace Database\Factories;

use App\Models\Produto;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProdutoFactory extends Factory
{
    protected $model = Produto::class;

    public function definition(): array
    {
        return [
            'referencia' => 'REF-' . str_pad($this->faker->unique()->numberBetween(1, 999), 3, '0', STR_PAD_LEFT),
            'descricao' => $this->faker->sentence(3),
            'tipo' => $this->faker->randomElement(['Produto', 'Serviço']),
            'disponibilidade' => $this->faker->randomElement(['Disponível', 'Indisponível']),
            'quantidade' => $this->faker->numberBetween(0, 100),
            'valor_unitario' => $this->faker->randomFloat(2, 10, 1000),
            'valor_atacado' => $this->faker->randomFloat(2, 5, 500),
            'valor_compra' => $this->faker->randomFloat(2, 5, 500),
            'categoria' => $this->faker->word,
            'slug' => $this->faker->slug,
            'imagem' => $this->faker->imageUrl(640, 480, 'products', true),
            'estoque_minimo' => $this->faker->numberBetween(0, 20),
            'ativo' => $this->faker->boolean(80),
            'destaque' => $this->faker->boolean(20),
            'novo' => $this->faker->boolean(30),
            'mais_vendido' => $this->faker->boolean(10),
            'visualizacoes' => $this->faker->numberBetween(0, 1000),
        ];
    }
}
