<?php

namespace Database\Factories;

use App\Models\Pedido;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class PedidoFactory extends Factory
{
    protected $model = Pedido::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'numero_pedido' => 'PED-' . strtoupper($this->faker->unique()->bothify('####??')),
            'total' => $this->faker->randomFloat(2, 50, 5000),
            'status' => $this->faker->randomElement(['pendente', 'pago', 'enviado', 'entregue', 'cancelado']),
            'endereco_entrega' => $this->faker->address(),
            'forma_pagamento' => $this->faker->randomElement(['cartao_credito', 'boleto', 'pix']),
            'data_entrega' => null,
            'observacoes' => $this->faker->optional()->sentence(),
            'created_at' => now(),
            'updated_at' => now()
        ];
    }

    public function pendente(): Factory
    {
        return $this->state(function (array $attributes) {
            return ['status' => 'pendente'];
        });
    }

    public function pago(): Factory
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'pago',
                'data_pagamento' => now()
            ];
        });
    }

    public function entregue(): Factory
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'entregue',
                'data_entrega' => now()
            ];
        });
    }

    public function cancelado(): Factory
    {
        return $this->state(function (array $attributes) {
            return ['status' => 'cancelado'];
        });
    }

    public function altoValor(): Factory
    {
        return $this->state(function (array $attributes) {
            return [
                'total' => $this->faker->randomFloat(2, 1000, 10000)
            ];
        });
    }
}