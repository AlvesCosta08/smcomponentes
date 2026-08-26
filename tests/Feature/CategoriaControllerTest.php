<?php

namespace Tests\Feature;

use App\Models\Categoria;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoriaControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Criar um usuário admin para testes
        $this->admin = User::factory()->create([
            'email' => 'admin@teste.com',
            'password' => bcrypt('password123'),
            'is_admin' => true,
        ]);
    }

    /** @test */
    public function usuario_pode_listar_categorias()
    {
        Categoria::factory()->count(3)->create();

        $response = $this->get('/categorias');

        $response->assertStatus(200);
        $response->assertViewHas('categorias');
    }

    /** @test */
    public function usuario_pode_ver_detalhes_de_uma_categoria()
    {
        $categoria = Categoria::factory()->create();

        $response = $this->get("/categorias/{$categoria->id}");

        $response->assertStatus(200);
        $response->assertViewHas('categoria');
        $response->assertSee($categoria->nome);
    }

    /** @test */
    public function admin_pode_criar_uma_categoria()
    {
        $this->actingAs($this->admin);

        $response = $this->post('/categorias', [
            'nome' => 'Nova Categoria',
            'slug' => 'nova-categoria',
            'descricao' => 'Descrição da categoria',
            'ativo' => true,
        ]);

        $response->assertRedirect('/categorias');
        $this->assertDatabaseHas('categorias', [
            'nome' => 'Nova Categoria',
            'slug' => 'nova-categoria',
        ]);
    }

    /** @test */
    public function admin_pode_atualizar_uma_categoria()
    {
        $this->actingAs($this->admin);
        
        $categoria = Categoria::factory()->create([
            'nome' => 'Categoria Antiga',
        ]);

        $response = $this->put("/categorias/{$categoria->id}", [
            'nome' => 'Categoria Atualizada',
            'slug' => 'categoria-atualizada',
            'descricao' => 'Nova descrição',
            'ativo' => true,
        ]);

        $response->assertRedirect('/categorias');
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

        $response = $this->delete("/categorias/{$categoria->id}");

        $response->assertRedirect('/categorias');
        $this->assertSoftDeleted('categorias', [
            'id' => $categoria->id,
        ]);
    }

    /** @test */
    public function usuario_nao_pode_criar_categoria_sem_permissao()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->post('/categorias', [
            'nome' => 'Nova Categoria',
        ]);

        $response->assertStatus(403); // Forbidden
    }

    /** @test */
    public function categoria_validacao_requer_nome()
    {
        $this->actingAs($this->admin);

        $response = $this->post('/categorias', [
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

        $response = $this->post('/categorias', [
            'nome' => 'Categoria Teste',
            'slug' => 'slug-unico',
        ]);

        $response->assertSessionHasErrors('slug');
    }
}
