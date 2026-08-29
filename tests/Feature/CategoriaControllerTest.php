<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Categoria;
use App\Models\Produto;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CategoriaControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('db:seed', ['--class' => 'RoleSeeder', '--force' => true]);
        
        $this->admin = User::factory()->create([
            'email' => 'admin@teste.com',
            'password' => bcrypt('password123'),
            'ativo' => true,
            'name' => 'Admin Teste',
        ]);
        $this->admin->assignRole('Admin');
    }

    /** @test */
    public function usuario_pode_listar_categorias()
    {
        Categoria::factory()->count(3)->create();
        
        $response = $this->get('/produtos');
        $response->assertStatus(200);
    }

    /** @test */
    public function usuario_pode_ver_detalhes_de_uma_categoria()
    {
        // ✅ CORRIGIDO: Criar categoria
        $categoria = Categoria::factory()->create([
            'slug' => 'categoria-teste',
            'ativo' => true,
        ]);
        
        // ✅ CORRIGIDO: Criar produtos para a categoria
        Produto::factory()->count(3)->create([
            'categoria_id' => $categoria->id,
            'ativo' => true,
        ]);
        
        // ✅ CORRIGIDO: Usar o SLUG
        $response = $this->get("/produtos/categoria/{$categoria->slug}");
        
        $response->assertStatus(200);
        $response->assertViewIs('produtos.index');
        $response->assertViewHas('produtos');
        $response->assertViewHas('titulo');
    }

    /** @test */
    public function admin_pode_criar_uma_categoria()
    {
        $this->actingAs($this->admin);
        
        $response = $this->post('/admin/categorias', [
            'nome' => 'Nova Categoria',
            'slug' => 'nova-categoria',
            'descricao' => 'Descrição da categoria',
            'ativo' => true,
        ]);
        
        $response->assertStatus(302);
        $response->assertRedirect('/admin/categorias');
        
        $this->assertDatabaseHas('categorias', [
            'nome' => 'Nova Categoria',
            'slug' => 'nova-categoria',
        ]);
    }

    /** @test */
    public function admin_pode_atualizar_uma_categoria()
    {
        $this->actingAs($this->admin);
        
        $categoria = Categoria::factory()->create(['nome' => 'Categoria Antiga']);
        
        $response = $this->put("/admin/categorias/{$categoria->id}", [
            'nome' => 'Categoria Atualizada',
            'slug' => 'categoria-atualizada',
            'descricao' => 'Nova descrição',
            'ativo' => true,
        ]);
        
        $response->assertStatus(302);
        $response->assertRedirect('/admin/categorias');
        
        $this->assertDatabaseHas('categorias', [
            'id' => $categoria->id,
            'nome' => 'Categoria Atualizada',
        ]);
    }

    /** @test */
    public function admin_pode_deletar_uma_categoria()
    {
        $this->actingAs($this->admin);
        
        $categoria = Categoria::factory()->create();
        
        $response = $this->delete("/admin/categorias/{$categoria->id}");
        
        $response->assertStatus(302);
        $response->assertRedirect('/admin/categorias');
        
        $this->assertDatabaseMissing('categorias', ['id' => $categoria->id]);
    }

    /** @test */
    public function usuario_nao_pode_criar_categoria_sem_permissao()
    {
        $user = User::factory()->create();
        $user->assignRole('Cliente');
        $this->actingAs($user);

        $response = $this->post('/admin/categorias', [
            'nome' => 'Nova Categoria',
        ]);

        $response->assertStatus(403);
    }

    /** @test */
    public function categoria_validacao_requer_nome()
    {
        $this->actingAs($this->admin);

        $response = $this->post('/admin/categorias', [
            'nome' => '',
            'slug' => 'slug-teste',
        ]);

        $response->assertSessionHasErrors('nome');
    }

    /** @test */
    public function categoria_validacao_requer_slug_unico()
    {
        $this->actingAs($this->admin);
        
        Categoria::factory()->create(['slug' => 'slug-unico']);

        $response = $this->post('/admin/categorias', [
            'nome' => 'Categoria Teste',
            'slug' => 'slug-unico',
        ]);

        $response->assertSessionHasErrors('slug');
    }

    /** @test */
    public function categoria_nao_encontrada_retorna_404()
    {
        $response = $this->get('/produtos/categoria/categoria-inexistente');
        
        $response->assertStatus(404);
    }

    /** @test */
    public function admin_pode_toggle_categoria_status()
    {
        $this->actingAs($this->admin);
        
        $categoria = Categoria::factory()->create(['ativo' => true]);
        
        $response = $this->post("/admin/categorias/{$categoria->id}/toggle");
        
        $response->assertStatus(302);
        $response->assertRedirect('/admin/categorias');
        
        $this->assertDatabaseHas('categorias', [
            'id' => $categoria->id,
            'ativo' => false,
        ]);
    }

    /** @test */
    public function admin_pode_reordenar_categorias()
    {
        $this->actingAs($this->admin);
        
        $categoria1 = Categoria::factory()->create(['ordem' => 1]);
        $categoria2 = Categoria::factory()->create(['ordem' => 2]);
        
        $response = $this->post('/admin/categorias/reorder', [
            'ordem' => [$categoria2->id, $categoria1->id],
        ]);
        
        $response->assertStatus(200);
        
        $this->assertDatabaseHas('categorias', [
            'id' => $categoria1->id,
            'ordem' => 2,
        ]);
        $this->assertDatabaseHas('categorias', [
            'id' => $categoria2->id,
            'ordem' => 1,
        ]);
    }
}