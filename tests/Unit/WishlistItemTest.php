<?php

namespace Tests\Unit;

use App\Models\WishlistItem;
use App\Models\Wishlist;
use App\Models\Produto;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WishlistItemTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function pode_criar_um_item_da_wishlist()
    {
        $item = WishlistItem::factory()->create();

        $this->assertDatabaseHas('wishlist_items', [
            'id' => $item->id
        ]);
    }

    /** @test */
    public function item_pertence_a_wishlist()
    {
        $wishlist = Wishlist::factory()->create();
        $item = WishlistItem::factory()->create(['wishlist_id' => $wishlist->id]);
        
        $this->assertInstanceOf(Wishlist::class, $item->wishlist);
    }

    /** @test */
    public function item_pertence_a_produto()
    {
        $produto = Produto::factory()->create();
        $item = WishlistItem::factory()->create(['produto_id' => $produto->id]);
        
        $this->assertInstanceOf(Produto::class, $item->produto);
    }
}