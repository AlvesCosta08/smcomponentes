<?php

namespace Tests\Feature;

use App\Models\Produto;
use App\Models\User;
use App\Models\Wishlist;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WishlistTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create();
        $this->produto = Produto::factory()->create();
        
        // Criar wishlist para o usuário
        $this->wishlist = Wishlist::factory()->create([
            'user_id' => $this->user->id,
        ]);
    }

    /** @test */
    public function usuario_pode_ver_sua_wishlist()
    {
        $this->actingAs($this->user);

        $response = $this->get('/cliente/wishlist');

        $response->assertStatus(200);
        $response->assertViewHas('wishlist');
    }

    /** @test */
    public function usuario_pode_adicionar_produto_a_wishlist()
    {
        $this->actingAs($this->user);

        $response = $this->post('/cliente/wishlist/adicionar', [
            'produto_id' => $this->produto->id,
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('wishlist_items', [
            'wishlist_id' => $this->wishlist->id,
            'produto_id' => $this->produto->id,
        ]);
    }

    /** @test */
    public function usuario_pode_remover_produto_da_wishlist()
    {
        $this->actingAs($this->user);
        
        // Primeiro adicionar
        $this->post('/cliente/wishlist/adicionar', [
            'produto_id' => $this->produto->id,
        ]);

        $response = $this->post('/cliente/wishlist/remover', [
            'produto_id' => $this->produto->id,
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseMissing('wishlist_items', [
            'produto_id' => $this->produto->id,
        ]);
    }

    /** @test */
    public function usuario_nao_autenticado_redirecionado_para_login()
    {
        $response = $this->get('/cliente/wishlist');
        $response->assertRedirect('/login');
    }
}
