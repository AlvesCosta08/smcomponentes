<?php

namespace Tests\Feature;

use App\Models\Pedido;
use App\Models\Produto;
use App\Models\User;
use App\Models\PedidoItem;
use App\Domain\Pedidos\Enums\StatusPedidoEnum;
use App\Domain\Pedidos\Enums\StatusPagamentoEnum;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PedidoControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->artisan('db:seed', ['--class' => 'RoleSeeder', '--force' => true]);
        
        $this->user = User::factory()->create();
        $this->produto = Produto::factory()->create([
            'quantidade' => 10,
            'valor_atacado' => 100.00,
            'descricao' => 'Produto Teste',
        ]);
    }

    /** @test */
    public function usuario_pode_finalizar_pedido()
    {
        $this->actingAs($this->user);
        
        $quantidade = 2;
        // ✅ CORRIGIDO: Usar o valor REAL do produto
        $precoUnitario = $this->produto->valor_atacado;
        $totalEsperado = $precoUnitario * $quantidade;
        
        session()->put('carrinho', [
            [
                'produto_id' => $this->produto->id,
                'quantidade' => $quantidade,
                'preco' => $precoUnitario,
            ]
        ]);

        $response = $this->post(route('checkout.processar'), [
            'endereco_entrega' => 'Rua Teste, 123',
            'forma_pagamento' => 'pix',
        ]);

        $response->assertStatus(302);
        
        $pedido = Pedido::where('user_id', $this->user->id)->latest()->first();
        $this->assertNotNull($pedido, 'Pedido não foi criado');
        
        // ✅ CORRIGIDO: Verificar com o valor real
        $this->assertDatabaseHas('pedidos', [
            'user_id' => $this->user->id,
            'total' => $totalEsperado,
            'status' => StatusPedidoEnum::PENDENTE->value,
        ]);
        
        $this->assertDatabaseHas('pedido_itens', [
            'produto_id' => $this->produto->id,
            'quantidade' => $quantidade,
        ]);
    }

    /** @test */
    public function usuario_pode_ver_seus_pedidos()
    {
        $this->actingAs($this->user);
        
        Pedido::factory()->count(3)->create(['user_id' => $this->user->id]);

        $response = $this->get(route('cliente.pedidos.index'));

        $response->assertStatus(200);
        $response->assertViewHas('pedidos');
    }

    /** @test */
    public function usuario_pode_ver_detalhes_de_um_pedido()
    {
        $this->actingAs($this->user);
        
        $pedido = Pedido::factory()->create(['user_id' => $this->user->id]);
        PedidoItem::factory()->count(2)->create(['pedido_id' => $pedido->id]);

        $response = $this->get(route('cliente.pedidos.detalhes', $pedido));

        $response->assertStatus(200);
        $response->assertViewHas('pedido');
    }

    /** @test */
    public function usuario_nao_pode_ver_pedido_de_outro_usuario()
    {
        $this->actingAs($this->user);
        
        $outroUsuario = User::factory()->create();
        $pedido = Pedido::factory()->create(['user_id' => $outroUsuario->id]);

        $response = $this->get(route('cliente.pedidos.detalhes', $pedido));

        $response->assertStatus(403);
    }

    /** @test */
    public function usuario_pode_cancelar_pedido()
    {
        $this->actingAs($this->user);
        
        $pedido = Pedido::factory()->create([
            'user_id' => $this->user->id,
            'status' => StatusPedidoEnum::PENDENTE->value,
            'status_pagamento' => StatusPagamentoEnum::AGUARDANDO->value,
        ]);

        $response = $this->post(route('cliente.pedidos.cancelar', $pedido));

        $response->assertStatus(302);
        $response->assertRedirect(route('cliente.pedidos.index'));
        
        $this->assertDatabaseHas('pedidos', [
            'id' => $pedido->id,
            'status' => StatusPedidoEnum::CANCELADO->value,
            'status_pagamento' => StatusPagamentoEnum::CANCELADO->value,
        ]);
    }

    /** @test */
    public function usuario_nao_pode_cancelar_pedido_ja_pago()
    {
        $this->actingAs($this->user);
        
        $pedido = Pedido::factory()->create([
            'user_id' => $this->user->id,
            'status' => StatusPedidoEnum::PAGO->value,
            'status_pagamento' => StatusPagamentoEnum::APROVADO->value,
        ]);

        $response = $this->post(route('cliente.pedidos.cancelar', $pedido));

        $response->assertStatus(302);
        $response->assertSessionHas('error', 'Este pedido não pode ser cancelado.');
        
        $this->assertDatabaseHas('pedidos', [
            'id' => $pedido->id,
            'status' => StatusPedidoEnum::PAGO->value,
        ]);
    }

    /** @test */
    public function checkout_requer_endereco_de_entrega()
    {
        $this->actingAs($this->user);
        
        session()->put('carrinho', [
            [
                'produto_id' => $this->produto->id,
                'quantidade' => 1,
                'preco' => $this->produto->valor_atacado,
            ]
        ]);

        $response = $this->post(route('checkout.processar'), [
            'forma_pagamento' => 'pix',
        ]);

        $response->assertSessionHasErrors('endereco_entrega');
    }

    /** @test */
    public function checkout_requer_forma_de_pagamento()
    {
        $this->actingAs($this->user);
        
        session()->put('carrinho', [
            [
                'produto_id' => $this->produto->id,
                'quantidade' => 1,
                'preco' => $this->produto->valor_atacado,
            ]
        ]);

        $response = $this->post(route('checkout.processar'), [
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
            'descricao' => 'Produto Estoque',
        ]);
        
        session()->put('carrinho', [
            [
                'produto_id' => $produto->id,
                'quantidade' => 3,
                'preco' => $produto->valor_atacado,
            ]
        ]);

        $response = $this->post(route('checkout.processar'), [
            'endereco_entrega' => 'Rua Teste, 123',
            'forma_pagamento' => 'pix',
        ]);

        $this->assertDatabaseHas('pedidos', [
            'user_id' => $this->user->id,
        ]);

        $produto->refresh();
        $this->assertEquals(7, $produto->quantidade);
    }

    /** @test */
    public function usuario_nao_pode_finalizar_pedido_com_estoque_insuficiente()
    {
        $this->actingAs($this->user);
        
        $produto = Produto::factory()->create([
            'quantidade' => 2,
            'valor_atacado' => 100.00,
            'descricao' => 'Produto Teste',
        ]);
        
        session()->put('carrinho', [
            [
                'produto_id' => $produto->id,
                'quantidade' => 5,
                'preco' => $produto->valor_atacado,
            ]
        ]);

        $response = $this->post(route('checkout.processar'), [
            'endereco_entrega' => 'Rua Teste, 123',
            'forma_pagamento' => 'pix',
        ]);

        $response->assertSessionHas('error');
        $this->assertDatabaseCount('pedidos', 0);
    }

    /** @test */
    public function usuario_pode_ver_historico_de_pedidos()
    {
        $this->actingAs($this->user);
        
        Pedido::factory()->create([
            'user_id' => $this->user->id,
            'status' => StatusPedidoEnum::PENDENTE->value,
            'created_at' => now()->subDays(5)
        ]);
        
        Pedido::factory()->create([
            'user_id' => $this->user->id,
            'status' => StatusPedidoEnum::PAGO->value,
            'created_at' => now()->subDays(3)
        ]);
        
        Pedido::factory()->create([
            'user_id' => $this->user->id,
            'status' => StatusPedidoEnum::ENTREGUE->value,
            'created_at' => now()->subDays(1)
        ]);

        $response = $this->get(route('cliente.pedidos.index'));
        
        $response->assertStatus(200);
        $response->assertViewHas('pedidos');
        
        $pedidos = $response->viewData('pedidos');
        $this->assertEquals(3, $pedidos->count());
    }
}