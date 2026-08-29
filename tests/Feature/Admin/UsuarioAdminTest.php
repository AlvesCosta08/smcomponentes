<?php

namespace Tests\Feature\Admin;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UsuarioAdminTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // ✅ CORRIGIDO: Criar admin sem 'is_admin'
        $this->admin = User::factory()->create([
            'email' => 'admin@teste.com',
            'password' => bcrypt('password123'),
            'ativo' => true,
            'name' => 'Admin Teste',
        ]);
    }

    /** @test */
    public function admin_pode_listar_usuarios()
    {
        $this->actingAs($this->admin);
        
        User::factory()->count(5)->create();
        
        $response = $this->get('/admin/usuarios');
        $response->assertStatus(200);
        $response->assertViewHas('usuarios');
    }

    /** @test */
    public function admin_pode_editar_usuario()
    {
        $this->actingAs($this->admin);
        
        $userToEdit = User::factory()->create(['name' => 'João']);
        
        $response = $this->put("/admin/usuarios/{$userToEdit->id}", [
            'name' => 'João Silva',
            'email' => 'joao.silva@email.com'
        ]);
        
        $response->assertRedirect('/admin/usuarios');
        $this->assertDatabaseHas('users', [
            'id' => $userToEdit->id,
            'name' => 'João Silva',
            'email' => 'joao.silva@email.com'
        ]);
    }

    /** @test */
    public function admin_pode_deletar_usuario()
    {
        $this->actingAs($this->admin);
        
        $userToDelete = User::factory()->create();
        
        $response = $this->delete("/admin/usuarios/{$userToDelete->id}");
        
        $response->assertRedirect('/admin/usuarios');
        $this->assertDatabaseMissing('users', ['id' => $userToDelete->id]);
    }

    /** @test */
    public function usuario_comum_nao_pode_acessar_admin()
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        
        $response = $this->get('/admin/usuarios');
        $response->assertStatus(403); // Forbidden
    }
}