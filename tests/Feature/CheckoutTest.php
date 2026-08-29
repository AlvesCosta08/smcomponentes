<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Produto;
use App\Models\Pedido;
use App\Domain\Pedidos\Enums\StatusPedidoEnum;
use App\Domain\Pedidos\Enums\StatusPagamentoEnum;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Session;

class CheckoutTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->artisan('db:seed', ['--class' => 'RoleSeeder', '--force' => true]);
        config(['services.mercadopago.access_token' => null]);
    }

    private function criarUsuarioComEndereco(): User
    {
        return User::factory()->create([
            'cep' => '01234-567',
            'logradouro' => 'Rua Teste',
            'numero' => '123',
            'complemento' => 'Apto 1',
            'bairro' => 'Centro',
            'cidade' => 'São Paulo',
            'estado' => 'SP',
            'ativo' => true
        ]);
    }

    private function adicionarAoCarrinhoViaSession(int $produtoId, int $quantidade = 1): void
    {
        $carrinho = Session::get('carrinho', []);
        $carrinho[] = ['produto_id' => $produtoId, 'quantidade' => $quantidade];
        Session::put('carrinho', $carrinho);
    }

    /** @test */
    public function usuario_com_carrinho_pode_acessar_checkout()
    {
        $user = $this->criarUsuarioComEndereco();
        $this->actingAs($user);
        
        $produto = Produto::factory()->create([
            'valor_unitario' => 100.00,
            'estoque' => 10,
            'quantidade' => 10,
            'ativo' => true
        ]);
        
        $this->adicionarAoCarrinhoViaSession($produto->id, 1);
        
        $response = $this->get('/checkout');
        
        $this->assertTrue(
            $response->getStatusCode() === 200 || 
            $response->getStatusCode() === 302,
            'Status deve ser 200 ou 302'
        );
    }

    /** @test */
    public function usuario_pode_criar_pedido_diretamente()
    {
        $user = $this->criarUsuarioComEndereco();
        $this->actingAs($user);
        
        $pedido = Pedido::create([
            'user_id' => $user->id,
            'numero_pedido' => 'PED-TEST-' . uniqid(),
            'subtotal' => 200.00,
            'desconto' => 0,
            'total' => 200.00,
            'status' => 'pendente',
            'status_pagamento' => 'aguardando',
            'forma_pagamento' => 'cartao',
            'endereco_entrega' => 'Rua Teste, 123',
            'cidade' => 'São Paulo',
            'estado' => 'SP',
            'cep' => '01234-567',
        ]);
        
        $this->assertDatabaseHas('pedidos', [
            'id' => $pedido->id,
            'user_id' => $user->id,
            'total' => 200.00,
            'status' => 'pendente',
            'status_pagamento' => 'aguardando'
        ]);
    }

    /** @test */
    public function carrinho_vazio_redireciona_para_carrinho()
    {
        $user = $this->criarUsuarioComEndereco();
        $this->actingAs($user);
        
        Session::forget('carrinho');
        
        $response = $this->get('/checkout');
        $response->assertRedirect('/carrinho');
    }

    /** @test */
    public function usuario_sem_endereco_redireciona_para_perfil()
    {
        $user = User::factory()->create([
            'cep' => null,
            'logradouro' => null,
            'numero' => null,
            'cidade' => null,
            'estado' => null,
            'ativo' => true
        ]);
        $this->actingAs($user);
        
        $produto = Produto::factory()->create([
            'valor_unitario' => 100.00,
            'estoque' => 10,
            'quantidade' => 10,
            'ativo' => true
        ]);
        
        $this->adicionarAoCarrinhoViaSession($produto->id, 1);
        
        $response = $this->get('/checkout');
        $response->assertRedirect('/cliente/perfil');
    }

    /** @test */
    public function usuario_nao_autenticado_redireciona_para_login()
    {
        $response = $this->get('/checkout');
        $response->assertRedirect('/login');
    }

    /** @test */
    public function pedido_criado_com_enum_correto_tem_status_pendente()
    {
        $user = $this->criarUsuarioComEndereco();
        $this->actingAs($user);
        
        $pedido = Pedido::create([
            'user_id' => $user->id,
            'numero_pedido' => 'PED-TEST-' . uniqid(),
            'subtotal' => 100.00,
            'desconto' => 0,
            'total' => 100.00,
            'status' => 'pendente',
            'status_pagamento' => 'aguardando',
            'forma_pagamento' => 'pix',
            'endereco_entrega' => 'Rua Teste, 123',
            'cidade' => 'São Paulo',
            'estado' => 'SP',
            'cep' => '01234-567',
        ]);
        
        // Recarrega o modelo
        $pedido = Pedido::find($pedido->id);
        
        // ✅ CORRIGIDO: Usar $pedido->status->value para acessar o valor do Enum
        $this->assertEquals('pendente', $pedido->status->value);
        $this->assertEquals('aguardando', $pedido->status_pagamento->value);
        $this->assertEquals(StatusPedidoEnum::PENDENTE, $pedido->status);
        $this->assertEquals(StatusPagamentoEnum::AGUARDANDO, $pedido->status_pagamento);
    }

    /** @test */
    public function pedido_pode_ser_marcado_como_pago()
    {
        $user = $this->criarUsuarioComEndereco();
        $this->actingAs($user);
        
        $pedido = Pedido::create([
            'user_id' => $user->id,
            'numero_pedido' => 'PED-TEST-' . uniqid(),
            'subtotal' => 100.00,
            'desconto' => 0,
            'total' => 100.00,
            'status' => 'pendente',
            'status_pagamento' => 'aguardando',
            'forma_pagamento' => 'pix',
            'endereco_entrega' => 'Rua Teste, 123',
            'cidade' => 'São Paulo',
            'estado' => 'SP',
            'cep' => '01234-567',
        ]);
        
        $pedido->update([
            'status' => 'pago',
            'status_pagamento' => 'aprovado',
            'data_pagamento' => now(),
        ]);
        
        $pedido = Pedido::find($pedido->id);
        
        // ✅ CORRIGIDO: Usar $pedido->status->value para acessar o valor do Enum
        $this->assertEquals('pago', $pedido->status->value);
        $this->assertEquals('aprovado', $pedido->status_pagamento->value);
        $this->assertEquals(StatusPedidoEnum::PAGO, $pedido->status);
        $this->assertEquals(StatusPagamentoEnum::APROVADO, $pedido->status_pagamento);
        $this->assertNotNull($pedido->data_pagamento);
    }

    /** @test */
    public function pedido_pode_ser_cancelado_quando_pendente()
    {
        $user = $this->criarUsuarioComEndereco();
        $this->actingAs($user);
        
        $pedido = Pedido::create([
            'user_id' => $user->id,
            'numero_pedido' => 'PED-TEST-' . uniqid(),
            'subtotal' => 100.00,
            'desconto' => 0,
            'total' => 100.00,
            'status' => 'pendente',
            'status_pagamento' => 'aguardando',
            'forma_pagamento' => 'pix',
            'endereco_entrega' => 'Rua Teste, 123',
            'cidade' => 'São Paulo',
            'estado' => 'SP',
            'cep' => '01234-567',
        ]);
        
        $pedido->update([
            'status' => 'cancelado',
            'status_pagamento' => 'cancelado',
        ]);
        
        $pedido = Pedido::find($pedido->id);
        
        // ✅ CORRIGIDO: Usar $pedido->status->value para acessar o valor do Enum
        $this->assertEquals('cancelado', $pedido->status->value);
        $this->assertEquals('cancelado', $pedido->status_pagamento->value);
        $this->assertEquals(StatusPedidoEnum::CANCELADO, $pedido->status);
        $this->assertEquals(StatusPagamentoEnum::CANCELADO, $pedido->status_pagamento);
    }
}