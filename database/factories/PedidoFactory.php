<?php

namespace Database\Factories;

use App\Models\Produto;
use App\Enums\DisponibilidadeEnum; // 1. Adicione este import
use Illuminate\Database\Eloquent\Factories\Factory;

class ProdutoFactory extends Factory
{
    protected $model = Produto::class;

    public function definition(): array
    {
        $valorCompra = $this->faker->randomFloat(2, 1, 100);
        $margem = $this->faker->randomElement([60, 80, 100, 120]);
        $ipi = $this->faker->randomFloat(2, 0, 10);
        
        // Cálculo simples para o factory (o PricingCalculator fará o trabalho real na aplicação)
        $valorAtacado = round($valorCompra / (1 - ($margem / 100)), 2);

        return [
            'categoria' => $this->faker->word,
            'referencia' => $this->faker->unique()->numerify('REF-#####'),
            'descricao' => $this->faker->sentence(3),
            'tipo' => 'UNI',
            'disponibilidade' => DisponibilidadeEnum::DISPONIVEL->value, // 2. Corrigido para usar o Enum
            'quantidade' => $this->faker->numberBetween(10, 100),
            'estoque_minimo' => 5,
            'valor_compra' => $valorCompra,
            'margem_lucro' => $margem,
            'ipi' => $ipi,
            'valor_atacado' => $valorAtacado,
            'valor_custo' => $valorCompra,
            'percentual_custo' => round(($valorCompra / $valorAtacado) * 100, 2),
            'ativo' => true,
            'slug' => $this->faker->slug,
        ];
    }
}