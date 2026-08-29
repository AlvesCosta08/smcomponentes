<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserFactory extends Factory
{
    protected static ?string $password;
    
    private static int $cpfIndex = 0;
    private static int $cnpjIndex = 0;

    /**
     * Lista de CPFs válidos (testados com a classe Cpf)
     */
    private static array $validCpfs = [
        '52998224725',
        '11144477735',
        '12345678909',
        '98765432100',
    ];

    /**
     * Lista de CNPJs válidos (testados com a classe Cnpj)
     */
    private static array $validCnpjs = [
        '11222333000181',
    ];

    private function getValidCpf(): string
    {
        $cpf = self::$validCpfs[self::$cpfIndex % count(self::$validCpfs)];
        self::$cpfIndex++;
        return $cpf;
    }

    private function getValidCnpj(): string
    {
        $cnpj = self::$validCnpjs[self::$cnpjIndex % count(self::$validCnpjs)];
        self::$cnpjIndex++;
        return $cnpj;
    }

    public function definition(): array
    {
        // 70% PJ (CNPJ), 30% PF (CPF)
        $isPessoaJuridica = $this->faker->boolean(70);
        
        $cpf = null;
        $cnpj = null;
        
        if ($isPessoaJuridica) {
            $cnpj = $this->getValidCnpj();
        } else {
            $cpf = $this->getValidCpf();
        }
        
        return [
            'name' => $isPessoaJuridica 
                ? $this->faker->company() . ' ' . $this->faker->companySuffix()
                : $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
            'telefone' => $this->faker->phoneNumber(),
            'celular' => $this->faker->phoneNumber(),
            'cpf' => $cpf,
            'cnpj' => $cnpj,
            'ie' => $isPessoaJuridica ? $this->faker->numerify('##########') : null,
            'data_nascimento' => $isPessoaJuridica ? null : $this->faker->date(),
            'cep' => $this->faker->postcode(),
            'logradouro' => $this->faker->streetName(),
            'numero' => $this->faker->buildingNumber(),
            'complemento' => $this->faker->optional()->secondaryAddress(),
            'bairro' => $this->faker->word(),
            'cidade' => $this->faker->city(),
            'estado' => $this->faker->stateAbbr(),
            'ativo' => true,
            'ultimo_acesso' => now(),
        ];
    }

    public function pessoaFisica(): static
    {
        return $this->state(fn (array $attributes) => [
            'cpf' => $this->getValidCpf(),
            'cnpj' => null,
            'name' => $this->faker->name(),
            'data_nascimento' => $this->faker->date(),
            'ie' => null,
        ]);
    }

    public function pessoaJuridica(): static
    {
        return $this->state(fn (array $attributes) => [
            'cpf' => null,
            'cnpj' => $this->getValidCnpj(),
            'name' => $this->faker->company() . ' ' . $this->faker->companySuffix(),
            'data_nascimento' => null,
            'ie' => $this->faker->numerify('##########'),
        ]);
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