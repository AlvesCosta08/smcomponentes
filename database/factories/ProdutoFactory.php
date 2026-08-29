<?php

namespace Database\Factories;

use App\Models\Produto;
use App\Models\Categoria;
use App\Enums\DisponibilidadeEnum;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProdutoFactory extends Factory
{
    protected $model = Produto::class;

    public function definition(): array
    {
        $valorCompra = $this->faker->randomFloat(2, 1, 100);
        $margem = $this->faker->randomElement([60, 65, 70, 75, 80, 85, 90, 95, 98]);
        $ipi = $this->faker->randomFloat(2, 0, 10);
        
        $valorAtacado = $margem < 100 
            ? round($valorCompra / (1 - ($margem / 100)), 2)
            : $valorCompra * 10;

        return [
            'categoria' => $this->faker->word,
            'categoria_id' => Categoria::factory(),
            'referencia' => $this->faker->unique()->numerify('REF-#####'),
            'descricao' => $this->faker->sentence(3),
            'tipo' => 'UNI',
            'disponibilidade' => DisponibilidadeEnum::DISPONIVEL->value,
            'quantidade' => $this->faker->numberBetween(10, 100),
            'estoque_minimo' => 5,
            'valor_compra' => $valorCompra,
            'margem_lucro' => $margem,
            'ipi' => $ipi,
            'valor_atacado' => $valorAtacado,
            'valor_unitario' => $valorAtacado, // ✅ CORRIGIDO: Nome correto da coluna
            'valor_custo' => $valorCompra,
            'percentual_custo' => $valorAtacado > 0 
                ? round(($valorCompra / $valorAtacado) * 100, 2) 
                : 0,
            'preco_promocional' => null,
            'ativo' => true,
            'destaque' => $this->faker->boolean(20),
            'novo' => $this->faker->boolean(30),
            'mais_vendido' => $this->faker->boolean(10),
            'visualizacoes' => $this->faker->numberBetween(0, 1000),
            'slug' => $this->faker->slug,
            'imagem' => null,
            'data_compra' => null,
            'ultima_atualizacao_estoque' => null,
            'ultima_visualizacao' => null,
            'estoque' => $this->faker->numberBetween(0, 100), // ✅ ADICIONADO: coluna estoque
            'status' => 'ativo', // ✅ ADICIONADO: coluna status
            'rating' => $this->faker->randomFloat(1, 0, 5), // ✅ ADICIONADO
            'total_avaliacoes' => $this->faker->numberBetween(0, 50), // ✅ ADICIONADO
            'galeria' => null, // ✅ ADICIONADO
        ];
    }

    public function disponivel(): Factory
    {
        return $this->state(function (array $attributes) {
            return [
                'ativo' => true,
                'disponibilidade' => DisponibilidadeEnum::DISPONIVEL->value,
                'quantidade' => $this->faker->numberBetween(10, 100),
                'estoque' => $this->faker->numberBetween(10, 100),
            ];
        });
    }

    public function indisponivel(): Factory
    {
        return $this->state(function (array $attributes) {
            return [
                'ativo' => false,
                'disponibilidade' => DisponibilidadeEnum::INDISPONIVEL->value,
                'quantidade' => 0,
                'estoque' => 0,
            ];
        });
    }

    public function estoqueBaixo(): Factory
    {
        return $this->state(function (array $attributes) {
            return [
                'ativo' => true,
                'disponibilidade' => DisponibilidadeEnum::ESTOQUE_BAIXO->value,
                'quantidade' => $this->faker->numberBetween(1, 5),
                'estoque' => $this->faker->numberBetween(1, 5),
            ];
        });
    }

    public function comQuantidade(int $quantidade): Factory
    {
        return $this->state(function (array $attributes) use ($quantidade) {
            $disponibilidade = $quantidade > 0 
                ? DisponibilidadeEnum::DISPONIVEL->value 
                : DisponibilidadeEnum::INDISPONIVEL->value;
            
            return [
                'quantidade' => $quantidade,
                'estoque' => $quantidade,
                'disponibilidade' => $disponibilidade,
                'ativo' => $quantidade > 0,
            ];
        });
    }

    public function comPromocao(): Factory
    {
        return $this->state(function (array $attributes) {
            $preco = $attributes['valor_atacado'] ?? $this->faker->randomFloat(2, 100, 1000);
            return [
                'valor_atacado' => $preco,
                'valor_unitario' => $preco,
                'preco_promocional' => round($preco * 0.7, 2),
            ];
        });
    }

    public function comImagem(): Factory
    {
        return $this->state(function (array $attributes) {
            return [
                'imagem' => 'produtos/' . $this->faker->imageUrl(640, 480, 'products', true),
            ];
        });
    }

    public function inativo(): Factory
    {
        return $this->state(function (array $attributes) {
            return [
                'ativo' => false,
                'disponibilidade' => DisponibilidadeEnum::INDISPONIVEL->value,
                'estoque' => 0,
            ];
        });
    }

    public function destaque(): Factory
    {
        return $this->state(function (array $attributes) {
            return [
                'destaque' => true,
                'ativo' => true,
                'disponibilidade' => DisponibilidadeEnum::DISPONIVEL->value,
            ];
        });
    }

    public function novo(): Factory
    {
        return $this->state(function (array $attributes) {
            return [
                'novo' => true,
                'ativo' => true,
                'disponibilidade' => DisponibilidadeEnum::DISPONIVEL->value,
            ];
        });
    }
}