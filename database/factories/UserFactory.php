<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserFactory extends Factory
{
    protected static ?string $password;

    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
            'telefone' => fake()->phoneNumber(),
            'data_nascimento' => fake()->date(),
            'cep' => fake()->postcode(),
            'logradouro' => fake()->streetName(),
            'numero' => fake()->buildingNumber(),
            'complemento' => fake()->optional()->secondaryAddress(),
            'bairro' => fake()->word(),
            'cidade' => fake()->city(),
            'estado' => fake()->stateAbbr(),
            'ativo' => true,
            'ultimo_acesso' => now(),
            // 'is_admin' => false,  // REMOVIDO - Coluna não existe na tabela
        ];
    }

    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    public function inativo(): static
    {
        return $this->state(fn (array $attributes) => [
            'ativo' => false,
        ]);
    }

    public function ativo(): static
    {
        return $this->state(fn (array $attributes) => [
            'ativo' => true,
        ]);
    }
}
