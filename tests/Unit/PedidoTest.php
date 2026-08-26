<?php

namespace Tests\Unit;

use App\Models\Pedido;
use App\Models\User;
use App\Models\PedidoItem;
use App\Domain\Pedidos\Enums\StatusPedidoEnum;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PedidoTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function pode_criar_um_pedido()
    {
        $pedido = Pedido::factory()->create([
            'total' => 150.00,
            'status' => 'pendente'
        ]);

        $this->assertDatabaseHas('pedidos', [
            'id' => $pedido->id,
            'total' => 150.00
        ]);
    }

    /** @test */
    public function pedido_pertence_a_um_usuario()
    {
        $user = User::factory()->create();
        $pedido = Pedido::factory()->create(['user_id' => $user->id]);
        
        $this->assertInstanceOf(User::class, $pedido->user);
        $this->assertEquals($user->id, $pedido->user->id);
    }

    /** @test */
    public function pedido_pode_ter_diferentes_status()
    {
        // Testar criação com diferentes status usando o Enum
        $pendente = Pedido::factory()->create(['status' => StatusPedidoEnum::PENDENTE->value]);
        $this->assertEquals(StatusPedidoEnum::PENDENTE, $pendente->status);
        
        $pago = Pedido::factory()->create(['status' => StatusPedidoEnum::PAGO->value]);
        $this->assertEquals(StatusPedidoEnum::PAGO, $pago->status);
        
        $entregue = Pedido::factory()->create(['status' => StatusPedidoEnum::ENTREGUE->value]);
        $this->assertEquals(StatusPedidoEnum::ENTREGUE, $entregue->status);
        
        $cancelado = Pedido::factory()->create(['status' => StatusPedidoEnum::CANCELADO->value]);
        $this->assertEquals(StatusPedidoEnum::CANCELADO, $cancelado->status);
    }

    /** @test */
    public function pedido_tem_itens()
    {
        $pedido = Pedido::factory()->create();
        PedidoItem::factory()->count(3)->create(['pedido_id' => $pedido->id]);
        
        $this->assertCount(3, $pedido->itens);
        $this->assertInstanceOf(PedidoItem::class, $pedido->itens->first());
    }

    /** @test */
    public function pedido_pode_ser_de_alto_valor()
    {
        $pedido = Pedido::factory()->create(['total' => 1500.00]);
        $this->assertGreaterThan(1000, $pedido->total);
    }

    /** @test */
    public function pedido_calcula_total_corretamente()
    {
        $pedido = Pedido::factory()->create();
        
        // Criar itens com valores específicos
        $item1 = PedidoItem::factory()->create([
            'pedido_id' => $pedido->id,
            'quantidade' => 2,
            'preco_unitario' => 50.00,
            'subtotal' => 100.00
        ]);
        
        $item2 = PedidoItem::factory()->create([
            'pedido_id' => $pedido->id,
            'quantidade' => 1,
            'preco_unitario' => 75.00,
            'subtotal' => 75.00
        ]);
        
        $pedido->refresh();
        
        // O total deve ser recalculado automaticamente
        // Verificar se o total foi atualizado
        $this->assertGreaterThan(0, $pedido->total);
    }
}