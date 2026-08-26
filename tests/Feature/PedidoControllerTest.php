<?php

namespace Tests\Feature;

use App\Models\Pedido;
use App\Models\Produto;
use App\Models\User;
use App\Models\PedidoItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PedidoControllerTest extends TestCase
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
    public function usuario_pode_finalizar_pedido()
    {
        $this->actingAs($this->user);
        
        // Adicionar ao carrinho
        session()->put('carrinho', [
            $this->produto->id => [
                'produto_id' => $this->produto->id,
                'quantidade' => 2,
                'preco' => $this->produto->valor_atacado,
            ]
        ]);

        $response = $this->post('/checkout', [
            'endereco_entrega' => 'Rua Teste, 123',
            'forma_pagamento' => 'cartao_credito',
        ]);

        $response->assertRedirect('/pedidos');
        
        $this->assertDatabaseHas('pedidos', [
            'user_id' => $this->user->id,
            'total' => 200.00,
            'status' => 'pendente',
        ]);
        
        $this->assertDatabaseHas('pedido_itens', [
            'produto_id' => $this->produto->id,
            'quantidade' => 2,
        ]);
    }

    /** @test */
    public function usuario_pode_ver_seus_pedidos()
    {
        $this->actingAs($this->user);
        
        Pedido::factory()->count(3)->create(['user_id' => $this->user->id]);

        $response = $this->get('/pedidos');

        $response->assertStatus(200);
        $response->assertViewHas('pedidos');
    }

    /** @test */
    public function usuario_pode_ver_detalhes_de_um_pedido()
    {
        $this->actingAs($this->user);
        
        $pedido = Pedido::factory()->create(['user_id' => $this->user->id]);
        PedidoItem::factory()->count(2)->create(['pedido_id' => $pedido->id]);

        $response = $this->get("/pedidos/{$pedido->id}");

        $response->assertStatus(200);
        $response->assertViewHas('pedido');
        $response->assertViewHas('itens');
    }

    /** @test */
    public function usuario_nao_pode_ver_pedido_de_outro_usuario()
    {
        $this->actingAs($this->user);
        
        $outroUsuario = User::factory()->create();
        $pedido = Pedido::factory()->create(['user_id' => $outroUsuario->id]);

        $response = $this->get("/pedidos/{$pedido->id}");

        $response->assertStatus(403); // Forbidden
    }

    /** @test */
    public function usuario_pode_cancelar_pedido()
    {
        $this->actingAs($this->user);
        
        $pedido = Pedido::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'pendente',
        ]);

        $response = $this->put("/pedidos/{$pedido->id}/cancelar");

        $response->assertRedirect('/pedidos');
        $this->assertDatabaseHas('pedidos', [
            'id' => $pedido->id,
            'status' => 'cancelado',
        ]);
    }

    /** @test */
    public function usuario_nao_pode_cancelar_pedido_ja_pago()
    {
        $this->actingAs($this->user);
        
        $pedido = Pedido::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'pago',
        ]);

        $response = $this->put("/pedidos/{$pedido->id}/cancelar");

        $response->assertStatus(400);
        $this->assertDatabaseHas('pedidos', [
            'id' => $pedido->id,
            'status' => 'pago',
        ]);
    }

    /** @test */
    public function checkout_requer_endereco_de_entrega()
    {
        $this->actingAs($this->user);
        
        session()->put('carrinho', [
            $this->produto->id => [
                'produto_id' => $this->produto->id,
                'quantidade' => 1,
                'preco' => $this->produto->valor_atacado,
            ]
        ]);

        $response = $this->post('/checkout', [
            'forma_pagamento' => 'cartao_credito',
        ]);

        $response->assertSessionHasErrors('endereco_entrega');
    }

    /** @test */
    public function checkout_requer_forma_de_pagamento()
    {
        $this->actingAs($this->user);
        
        session()->put('carrinho', [
            $this->produto->id => [
                'produto_id' => $this->produto->id,
                'quantidade' => 1,
                'preco' => $this->produto->valor_atacado,
            ]
        ]);

        $response = $this->post('/checkout', [
            'endereco_entrega' => 'Rua Teste, 123',
        ]);

        $response->assertSessionHasErrors('forma_pagamento');
    }

    /** @test */
    public function checkout_diminui_estoque_ao_finalizar()
    {
        $this->actingAs($this->user);
        
        $produto = Produto::factory()->create([
            'quantidade' => 10,
            'valor_atacado' => 100.00,
        ]);
        
        session()->put('carrinho', [
            $produto->id => [
                'produto_id' => $produto->id,
                'quantidade' => 3,
                'preco' => $produto->valor_atacado,
            ]
        ]);

        $this->post('/checkout', [
            'endereco_entrega' => 'Rua Teste, 123',
            'forma_pagamento' => 'cartao_credito',
        ]);

        $produto->refresh();
        $this->assertEquals(7, $produto->quantidade);
    }
}
