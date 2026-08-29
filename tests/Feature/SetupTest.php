<?php

namespace Tests\Feature;

use App\Models\Produto;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SetupTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function ambiente_de_teste_esta_configurado(): void
    {
        $this->assertEquals('testing', config('app.env'));
        $this->assertEquals('sqlite', config('database.default'));
        $this->assertEquals('array', config('mail.default'));
        $this->assertEquals('sync', config('queue.default'));
    }

    /** @test */
    public function factory_produto_esta_configurada(): void
    {
        $produto = Produto::factory()->create();

        $this->assertInstanceOf(Produto::class, $produto);
        $this->assertDatabaseHas('produtos', [
            'id' => $produto->id,
        ]);
    }
}

