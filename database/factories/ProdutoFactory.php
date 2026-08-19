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
            'categoria' => $this->faker->word(),
            'referencia' => 'REF-' . str_pad($this->faker->unique()->numberBetween(1, 999), 3, '0', STR_PAD_LEFT),
            'descricao' => $this->faker->sentence(3),
            'tipo' => $this->faker->randomElement(['Produto', 'Serviço']),
            'disponibilidade' => $this->faker->randomElement([
                Produto::DISPONIVEL,
                Produto::INDISPONIVEL,
                Produto::ESTOQUE_BAIXO
            ]),
            'quantidade' => $this->faker->numberBetween(1, 100),
            'valor_unitario' => $this->faker->randomFloat(2, 10, 1000),
            'valor_atacado' => $this->faker->randomFloat(2, 5, 500),
            'valor_compra' => $this->faker->randomFloat(2, 5, 500),
            'valor_custo' => $this->faker->randomFloat(2, 5, 500),
            'preco_promocional' => null,
            'ipi' => $this->faker->randomFloat(2, 0, 10),
            'percentual_custo' => $this->faker->randomFloat(2, 10, 50),
            'margem_lucro' => $this->faker->randomFloat(2, 10, 50),
            'estoque_minimo' => $this->faker->numberBetween(0, 20),
            'ativo' => $this->faker->boolean(80),
            'destaque' => $this->faker->boolean(20),
            'novo' => $this->faker->boolean(30),
            'mais_vendido' => $this->faker->boolean(10),
            'visualizacoes' => $this->faker->numberBetween(0, 1000),
            'slug' => $this->faker->slug,
            'imagem' => null,
            'categoria_id' => null,
            'data_compra' => null,
            'ultima_atualizacao_estoque' => null,
            'ultima_visualizacao' => null,
            'rating' => $this->faker->randomFloat(1, 0, 5),
            'total_avaliacoes' => $this->faker->numberBetween(0, 100),
        ];
    }

    public function disponivel(): Factory
    {
        return $this->state(function (array $attributes) {
            return [
                'ativo' => true,
                'disponibilidade' => Produto::DISPONIVEL,
                'quantidade' => $this->faker->numberBetween(10, 100),
            ];
        });
    }

    public function indisponivel(): Factory
    {
        return $this->state(function (array $attributes) {
            return [
                'ativo' => false,
                'disponibilidade' => Produto::INDISPONIVEL,
                'quantidade' => 0,
            ];
        });
    }

    public function estoqueBaixo(): Factory
    {
        return $this->state(function (array $attributes) {
            return [
                'ativo' => true,
                'disponibilidade' => Produto::ESTOQUE_BAIXO,
                'quantidade' => $this->faker->numberBetween(1, 5),
            ];
        });
    }

    public function comQuantidade(int $quantidade): Factory
    {
        return $this->state(function (array $attributes) use ($quantidade) {
            return [
                'quantidade' => $quantidade,
                'disponibilidade' => $quantidade > 0 ? Produto::DISPONIVEL : Produto::INDISPONIVEL,
                'ativo' => $quantidade > 0,
            ];
        });
    }
}
