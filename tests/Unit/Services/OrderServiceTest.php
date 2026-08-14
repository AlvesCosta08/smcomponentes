<?php

namespace Tests\Unit\Services;

use App\DTOs\OrderDTO;
use App\Exceptions\OutOfStockException;
use App\Models\Pedido;
use App\Models\Produto;
use App\Models\User;
use App\Repositories\PedidoRepository;
use App\Services\OrderService;
use App\Services\StockService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class OrderServiceTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected OrderService $orderService;

    protected function setUp(): void
    {
        parent::setUp();
        $repository = new PedidoRepository();
        $stockService = new StockService();
        $this->orderService = new OrderService($repository, $stockService);
    }

    private function createOrderDTO(array $overrides = []): OrderDTO
    {
        return new OrderDTO(
            endereco: $overrides['endereco'] ?? 'Rua Teste, 123',
            cidade: $overrides['cidade'] ?? 'São Paulo',
            estado: $overrides['estado'] ?? 'SP',
            cep: $overrides['cep'] ?? '01234567',
            forma_pagamento: $overrides['forma_pagamento'] ?? 'cartao',
            telefone: $overrides['telefone'] ?? '11999999999',
            observacoes: $overrides['observacoes'] ?? 'Sem observações',
            numero: $overrides['numero'] ?? '123',
            complemento: $overrides['complemento'] ?? 'Apto 45',
            bairro: $overrides['bairro'] ?? 'Centro',
        );
    }

    private function createCart(Produto $produto, int $quantidade = 2): array
    {
        return [
            $produto->id => [
                'id' => $produto->id,
                'nome' => $produto->descricao,
                'quantidade' => $quantidade,
                'preco' => $produto->valor_unitario ?? 99.90,
                'preco_promocional' => $produto->preco_promocional,
            ]
        ];
    }

    /** @test */
    public function pode_criar_pedido_com_sucesso()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $produto = Produto::factory()->create([
            'quantidade' => 10,
            'valor_unitario' => 99.90,
            'descricao' => 'Produto Teste',
        ]);

        $dto = $this->createOrderDTO();
        $carrinho = $this->createCart($produto);

        $resultado = $this->orderService->createOrder($dto, $carrinho);
        
        $this->assertNotNull($resultado);
        $this->assertEquals(199.80, $resultado->total);
        $this->assertEquals(1, Pedido::count());
        $this->assertEquals(8, $produto->fresh()->quantidade);
    }

    /** @test */
    public function lanca_exception_quando_produto_nao_encontrado_no_carrinho()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Produto não encontrado');

        $user = User::factory()->create();
        $this->actingAs($user);

        $dto = $this->createOrderDTO();
        $carrinho = [
            999 => [
                'id' => 999,
                'nome' => 'Produto Inexistente',
                'quantidade' => 1,
                'preco' => 99.90,
            ]
        ];

        $this->orderService->createOrder($dto, $carrinho);
    }

    /** @test */
    public function lanca_exception_quando_estoque_insuficiente()
    {
        $this->expectException(OutOfStockException::class);

        $user = User::factory()->create();
        $this->actingAs($user);

        $produto = Produto::factory()->create([
            'quantidade' => 2,
            'valor_unitario' => 99.90,
        ]);

        $dto = $this->createOrderDTO();
        $carrinho = $this->createCart($produto, 5);

        $this->orderService->createOrder($dto, $carrinho);
    }

    /** @test */
    public function pode_cancelar_pedido_pendente()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $produto = Produto::factory()->create(['quantidade' => 10]);
        
        $pedido = Pedido::factory()->create([
            'user_id' => $user->id,
            'status' => 'pendente',
            'numero_pedido' => 'PED-001',
            'subtotal' => 100.00,
            'total' => 100.00,
        ]);

        $resultado = $this->orderService->cancelOrder($pedido);
        
        $this->assertTrue($resultado);
        $this->assertEquals('cancelado', $pedido->fresh()->status);
    }

    /** @test */
    public function restaura_estoque_ao_cancelar_pedido()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $produto = Produto::factory()->create(['quantidade' => 10]);
        
        $pedido = Pedido::factory()->create([
            'user_id' => $user->id,
            'status' => 'pendente',
        ]);

        // Criar item do pedido
        $pedido->itens()->create([
            'produto_id' => $produto->id,
            'quantidade' => 3,
            'preco_unitario' => 99.90,
            'subtotal' => 299.70,
            'nome_produto' => $produto->descricao,
        ]);

        $this->orderService->cancelOrder($pedido);
        
        $this->assertEquals(10, $produto->fresh()->quantidade);
    }

    /** @test */
    public function lanca_exception_ao_cancelar_pedido_ja_entregue()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('não pode ser cancelado');

        $user = User::factory()->create();
        $this->actingAs($user);

        $pedido = Pedido::factory()->create([
            'user_id' => $user->id,
            'status' => 'entregue',
        ]);

        $this->orderService->cancelOrder($pedido);
    }

    /** @test */
    public function lanca_exception_ao_cancelar_pedido_ja_cancelado()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('não pode ser cancelado');

        $user = User::factory()->create();
        $this->actingAs($user);

        $pedido = Pedido::factory()->create([
            'user_id' => $user->id,
            'status' => 'cancelado',
        ]);

        $this->orderService->cancelOrder($pedido);
    }

    /** @test */
    public function pode_buscar_pedidos_do_usuario()
    {
        $user = User::factory()->create();
        Pedido::factory()->count(3)->create(['user_id' => $user->id]);
        Pedido::factory()->count(2)->create();

        $resultado = $this->orderService->getUserOrders($user->id, 10);
        
        $this->assertEquals(3, $resultado->total());
    }

    /** @test */
    public function pode_buscar_detalhes_do_pedido()
    {
        $user = User::factory()->create();
        $pedido = Pedido::factory()->create(['user_id' => $user->id]);

        $resultado = $this->orderService->getOrderDetails($pedido->id, $user->id);
        
        $this->assertNotNull($resultado);
        $this->assertEquals($pedido->id, $resultado->id);
    }

    /** @test */
    public function retorna_null_quando_pedido_nao_encontrado()
    {
        $user = User::factory()->create();
        
        $resultado = $this->orderService->getOrderDetails(999, $user->id);
        
        $this->assertNull($resultado);
    }

    /** @test */
    public function calcula_subtotal_corretamente()
    {
        $carrinho = [
            1 => [
                'id' => 1,
                'nome' => 'Produto 1',
                'quantidade' => 2,
                'preco' => 50.00,
            ],
            2 => [
                'id' => 2,
                'nome' => 'Produto 2',
                'quantidade' => 3,
                'preco' => 30.00,
            ],
        ];

        $reflection = new \ReflectionClass($this->orderService);
        $method = $reflection->getMethod('calculateSubtotal');
        $method->setAccessible(true);

        $resultado = $method->invoke($this->orderService, $carrinho);
        
        $this->assertEquals(190.00, $resultado);
    }
}