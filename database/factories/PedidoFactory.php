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
            'user_id' => User::factory(), // Garantir que user_id seja preenchido
            'numero_pedido' => 'PED-' . $this->faker->unique()->numberBetween(1000, 9999),
            'status' => $this->faker->randomElement(['pendente', 'pago', 'processando', 'enviado', 'entregue', 'cancelado']),
            'subtotal' => $this->faker->randomFloat(2, 50, 500),
            'total' => $this->faker->randomFloat(2, 50, 500),
            'forma_pagamento' => $this->faker->randomElement(['cartao', 'boleto', 'pix']),
            'endereco_entrega' => $this->faker->address(),
            'cidade' => $this->faker->city(),
            'estado' => $this->faker->stateAbbr(),
            'cep' => $this->faker->postcode(),
            'created_at' => $this->faker->dateTimeBetween('-30 days', 'now'),
        ];
    }

    public function pendente(): Factory
    {
        return $this->state(function (array $attributes) {
            return ['status' => 'pendente'];
        });
    }

    public function entregue(): Factory
    {
        return $this->state(function (array $attributes) {
            return ['status' => 'entregue'];
        });
    }

    public function cancelado(): Factory
    {
        return $this->state(function (array $attributes) {
            return ['status' => 'cancelado'];
        });
    }

    public function comUsuario(User $user): Factory
    {
        return $this->state(function (array $attributes) use ($user) {
            return ['user_id' => $user->id];
        });
    }
}
