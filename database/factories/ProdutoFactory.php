<?php

namespace Database\Factories;

use App\Models\Produto;
use App\Models\Categoria;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ProdutoFactory extends Factory
{
    protected $model = Produto::class;

    public function definition(): array
    {
        $descricao = $this->faker->sentence(3);
        $valorAtacado = $this->faker->randomFloat(2, 5, 500);

        return [
            // Categoria
            'categoria_id' => Categoria::factory(),

            // Identificação
            'referencia' => $this->faker->unique()->numerify('REF-#####'),
            'descricao' => $descricao,
            'slug' => Str::slug($descricao) . '-' . $this->faker->unique()->numberBetween(1000, 9999),
            'tipo' => 'UNI',
            'fornecedor' => $this->faker->optional()->company(),

            // Imagem
            'imagem' => null,

            // Estoque
            'quantidade' => $this->faker->numberBetween(10, 100),
            'estoque_minimo' => 5,

            // Preços
            'valor_unitario' => $valorAtacado,
            'valor_atacado' => $valorAtacado,
            'preco_promocional' => null,

            // Status
            'status' => 'disponivel',
            'ativo' => true,
            'destaque' => $this->faker->boolean(20),
            'novo' => $this->faker->boolean(30),
            'mais_vendido' => $this->faker->boolean(15),
            'visualizacoes' => $this->faker->numberBetween(0, 500),

            // Datas
            'data_compra' => null,
            'ultima_atualizacao_estoque' => null,
            'ultima_visualizacao' => null,
        ];
    }

    // ============================================
    // STATES
    // ============================================

    public function disponivel(): Factory
    {
        return $this->state(fn () => [
            'status' => 'disponivel',
            'ativo' => true,
            'quantidade' => $this->faker->numberBetween(10, 100),
        ]);
    }

    public function indisponivel(): Factory
    {
        return $this->state(fn () => [
            'status' => 'indisponivel',
            'ativo' => false,
            'quantidade' => 0,
        ]);
    }

    public function estoqueBaixo(): Factory
    {
        return $this->state(fn () => [
            'status' => 'disponivel',
            'ativo' => true,
            'quantidade' => $this->faker->numberBetween(1, 5),
        ]);
    }

    public function sobEncomenda(): Factory
    {
        return $this->state(fn () => [
            'status' => 'sob_encomenda',
            'ativo' => true,
            'quantidade' => 0,
        ]);
    }

    public function comQuantidade(int $quantidade): Factory
    {
        return $this->state(fn () => [
            'quantidade' => $quantidade,
            'status' => $quantidade > 0 ? 'disponivel' : 'indisponivel',
            'ativo' => $quantidade > 0,
        ]);
    }

    public function comPromocao(): Factory
    {
        return $this->state(function (array $attributes) {
            $preco = $attributes['valor_atacado'] ?? $this->faker->randomFloat(2, 100, 1000);
            return [
                'valor_unitario' => $preco,
                'valor_atacado' => $preco,
                'preco_promocional' => round($preco * 0.7, 2),
            ];
        });
    }

    public function comImagem(): Factory
    {
        return $this->state(fn () => [
            'imagem' => 'produtos/' . $this->faker->imageUrl(640, 480, 'products', true),
        ]);
    }

    public function inativo(): Factory
    {
        return $this->state(fn () => [
            'ativo' => false,
            'status' => 'indisponivel',
            'quantidade' => 0,
        ]);
    }

    public function destaque(): Factory
    {
        return $this->state(fn () => [
            'destaque' => true,
            'ativo' => true,
            'status' => 'disponivel',
        ]);
    }

    public function novo(): Factory
    {
        return $this->state(fn () => [
            'novo' => true,
            'ativo' => true,
            'status' => 'disponivel',
        ]);
    }
}