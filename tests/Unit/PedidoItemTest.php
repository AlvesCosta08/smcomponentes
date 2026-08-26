<?php

namespace Tests\Unit;

use App\Models\PedidoItem;
use App\Models\Pedido;
use App\Models\Produto;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PedidoItemTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function pode_criar_um_item_do_pedido()
    {
        $item = PedidoItem::factory()->create([
            'quantidade' => 2,
            'subtotal' => 100.00
        ]);

        $this->assertDatabaseHas('pedido_itens', [
            'id' => $item->id,
            'quantidade' => 2
        ]);
    }

    /** @test */
    public function item_pertence_a_pedido()
    {
        $pedido = Pedido::factory()->create();
        $item = PedidoItem::factory()->create(['pedido_id' => $pedido->id]);
        
        $this->assertInstanceOf(Pedido::class, $item->pedido);
    }

    /** @test */
    public function item_pertence_a_produto()
    {
        $produto = Produto::factory()->create();
        $item = PedidoItem::factory()->create(['produto_id' => $produto->id]);
        
        $this->assertInstanceOf(Produto::class, $item->produto);
    }
}