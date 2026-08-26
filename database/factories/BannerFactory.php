<?php

namespace Database\Factories;

use App\Models\Banner;
use Illuminate\Database\Eloquent\Factories\Factory;

class BannerFactory extends Factory
{
    protected $model = Banner::class;

    public function definition(): array
    {
        return [
            'titulo' => $this->faker->sentence(3),
            'subtitulo' => $this->faker->optional()->sentence(5),
            'imagem' => $this->faker->imageUrl(1200, 400, 'banners'),
            'link' => $this->faker->optional()->url(),
            'ordem' => $this->faker->numberBetween(0, 10),
            'ativo' => $this->faker->boolean(80),
            'created_at' => now(),
            'updated_at' => now()
        ];
    }

    public function ativo(): Factory
    {
        return $this->state(function (array $attributes) {
            return ['ativo' => true];
        });
    }

    public function inativo(): Factory
    {
        return $this->state(function (array $attributes) {
            return ['ativo' => false];
        });
    }

    public function comLink(): Factory
    {
        return $this->state(function (array $attributes) {
            return ['link' => $this->faker->url()];
        });
    }
}