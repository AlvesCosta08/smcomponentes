<?php

namespace Database\Factories;

use App\Models\Categoria;
use Illuminate\Database\Eloquent\Factories\Factory;

class CategoriaFactory extends Factory
{
    protected $model = Categoria::class;

    public function definition()
    {
        return [
            'nome' => $this->faker->unique()->word(),
            'slug' => $this->faker->unique()->slug(),
            'descricao' => $this->faker->sentence(),
            'ativo' => $this->faker->boolean(80),
            'created_at' => now(),
            'updated_at' => now()
        ];
    }
}