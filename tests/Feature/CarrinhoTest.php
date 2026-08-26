<?php

namespace Tests\Feature;

use App\Models\Produto;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CarrinhoTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create();
        $this->produto = Produto::factory()->create([
            'quantidade' => 10,
            'valor_atacado' => 100.00,
        ]);
    }

    /** @test */
    public function usuario_pode_ver_carrinho()
    {
        $this->actingAs($this->user);

        $response = $this->get('/carrinho');

        $response->assertStatus(200);
        $response->assertViewIs('carrinho.index');
    }

    /** @test */
    public function usuario_pode_adicionar_produto_ao_carrinho()
    {
        $this->actingAs($this->user);

        $response = $this->post('/carrinho/adicionar', [
            'produto_id' => $this->produto->id,
            'quantidade' => 2,
        ]);

        $response->assertStatus(302); // Redirect
        $this->assertTrue(session()->has('carrinho'));
    }

    /** @test */
    public function usuario_pode_atualizar_quantidade_no_carrinho()
    {
        $this->actingAs($this->user);
        
        // Primeiro adicionar
        $this->post('/carrinho/adicionar', [
            'produto_id' => $this->produto->id,
            'quantidade' => 2,
        ]);

        $response = $this->put('/carrinho/atualizar/' . $this->produto->id, [
            'quantidade' => 5,
        ]);

        $response->assertStatus(302);
    }

    /** @test */
    public function usuario_pode_remover_produto_do_carrinho()
    {
        $this->actingAs($this->user);
        
        // Primeiro adicionar
        $this->post('/carrinho/adicionar', [
            'produto_id' => $this->produto->id,
            'quantidade' => 2,
        ]);

        $response = $this->delete('/carrinho/remover/' . $this->produto->id);

        $response->assertStatus(302);
    }

    /** @test */
    public function usuario_pode_esvaziar_carrinho()
    {
        $this->actingAs($this->user);
        
        $this->post('/carrinho/adicionar', [
            'produto_id' => $this->produto->id,
            'quantidade' => 2,
        ]);

        $response = $this->delete('/carrinho/limpar');

        $response->assertStatus(302);
    }

    /** @test */
    public function usuario_nao_autenticado_redirecionado_para_login()
    {
        $response = $this->get('/carrinho');
        $response->assertRedirect('/login');
    }
}
