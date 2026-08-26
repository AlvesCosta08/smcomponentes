<?php

namespace Tests\Unit;

use App\Models\Wishlist;
use App\Models\User;
use App\Models\WishlistItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WishlistTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function pode_criar_uma_wishlist()
    {
        $wishlist = Wishlist::factory()->create([
            'nome' => 'Minha Lista'
        ]);

        $this->assertDatabaseHas('wishlists', [
            'id' => $wishlist->id,
            'nome' => 'Minha Lista'
        ]);
    }

    /** @test */
    public function wishlist_pertence_a_um_usuario()
    {
        $user = User::factory()->create();
        $wishlist = Wishlist::factory()->create(['user_id' => $user->id]);
        
        $this->assertInstanceOf(User::class, $wishlist->user);
    }
}