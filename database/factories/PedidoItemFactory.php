<?php

namespace Database\Factories;

use App\Models\PedidoItem;
use App\Models\Pedido;
use App\Models\Produto;
use Illuminate\Database\Eloquent\Factories\Factory;

class PedidoItemFactory extends Factory
{
    protected $model = PedidoItem::class;

    public function definition(): array
    {
        $quantidade = $this->faker->numberBetween(1, 5);
        $precoUnitario = $this->faker->randomFloat(2, 10, 500);
        
        return [
            'pedido_id' => Pedido::factory(),
            'produto_id' => Produto::factory(),
            'quantidade' => $quantidade,
            'preco_unitario' => $precoUnitario,
            'subtotal' => $precoUnitario * $quantidade,
            'created_at' => now(),
            'updated_at' => now()
        ];
    }

    public function comQuantidade(int $quantidade): Factory
    {
        return $this->state(function (array $attributes) use ($quantidade) {
            return ['quantidade' => $quantidade];
        });
    }

    public function comPrecoUnitario(float $preco): Factory
    {
        return $this->state(function (array $attributes) use ($preco) {
            return [
                'preco_unitario' => $preco,
                'subtotal' => $preco * $this->faker->numberBetween(1, 5)
            ];
        });
    }
}