<?php

namespace Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SetupTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function ambiente_de_teste_esta_configurado()
    {
        $this->assertEquals('testing', config('app.env'));
        $this->assertEquals('sqlite', config('database.default'));
        $this->assertEquals('array', config('mail.default'));
        $this->assertEquals('sync', config('queue.default'));
    }

    /** @test */
    public function factory_produto_esta_configurada()
    {
        $produto = \App\Models\Produto::factory()->create();
        $this->assertInstanceOf(\App\Models\Produto::class, $produto);
        $this->assertDatabaseHas('produtos', ['id' => $produto->id]);
    }
}