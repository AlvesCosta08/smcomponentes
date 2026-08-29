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
        
        $this->artisan('db:seed', ['--class' => 'RoleSeeder', '--force' => true]);
        
        $this->user = User::factory()->create();
        $this->produto = Produto::factory()->create();
        
        // Criar wishlist padrão para o usuário
        $this->wishlist = Wishlist::factory()->create([
            'user_id' => $this->user->id,
            'is_default' => true,
            'nome' => 'Minha Lista',
        ]);
    }

    /** @test */
    public function usuario_pode_ver_sua_wishlist()
    {
        $this->actingAs($this->user);

        $response = $this->get('/cliente/wishlist');

        $response->assertStatus(200);
        // ✅ CORRIGIDO: A view retorna 'wishlists' (plural) ou 'wishlist' (singular)
        // Verifica se a view tem a variável correta
        $response->assertViewHas('wishlists');
    }

    /** @test */
    public function usuario_pode_adicionar_produto_a_wishlist()
    {
        $this->actingAs($this->user);

        $response = $this->post('/cliente/wishlist/adicionar', [
            'produto_id' => $this->produto->id,
            // ✅ ADICIONADO: Passar o wishlist_id para garantir que é a wishlist correta
            'wishlist_id' => $this->wishlist->id,
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => 'Produto adicionado à lista de desejos!'
        ]);
        
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
            'wishlist_id' => $this->wishlist->id,
        ]);

        $response = $this->post('/cliente/wishlist/remover', [
            'produto_id' => $this->produto->id,
            'wishlist_id' => $this->wishlist->id,
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => 'Produto removido da lista de desejos!'
        ]);
        
        $this->assertDatabaseMissing('wishlist_items', [
            'wishlist_id' => $this->wishlist->id,
            'produto_id' => $this->produto->id,
        ]);
    }

    /** @test */
    public function usuario_nao_autenticado_redirecionado_para_login()
    {
        $response = $this->get('/cliente/wishlist');
        $response->assertRedirect('/login');
    }

    /** @test */
    public function usuario_pode_criar_nova_wishlist()
    {
        $this->actingAs($this->user);

        $response = $this->post('/cliente/wishlist', [
            'nome' => 'Lista de Presentes',
            'descricao' => 'Presentes para o aniversário',
            'is_public' => false,
        ]);

        $response->assertStatus(302);
        $this->assertDatabaseHas('wishlists', [
            'user_id' => $this->user->id,
            'nome' => 'Lista de Presentes',
            'is_default' => false,
        ]);
    }

    /** @test */
    public function usuario_pode_editar_sua_wishlist()
    {
        $this->actingAs($this->user);

        $response = $this->put("/cliente/wishlist/{$this->wishlist->id}", [
            'nome' => 'Lista Atualizada',
            'descricao' => 'Nova descrição',
            'is_public' => true,
        ]);

        $response->assertStatus(302);
        $this->assertDatabaseHas('wishlists', [
            'id' => $this->wishlist->id,
            'nome' => 'Lista Atualizada',
            'is_public' => true,
        ]);
    }

    /** @test */
    public function usuario_pode_deletar_sua_wishlist()
    {
        $this->actingAs($this->user);
        
        // Criar uma wishlist não padrão para deletar
        $wishlist = Wishlist::factory()->create([
            'user_id' => $this->user->id,
            'is_default' => false,
            'nome' => 'Lista para Deletar',
        ]);

        $response = $this->delete("/cliente/wishlist/{$wishlist->id}");

        $response->assertStatus(302);
        $this->assertDatabaseMissing('wishlists', [
            'id' => $wishlist->id,
        ]);
    }
}
