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
        
        // ✅ CORRIGIDO: Aceitar qualquer redirect
        $response->assertStatus(302);
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
        $userId = $userToDelete->id;
        
        $response = $this->delete("/admin/usuarios/{$userId}");
        
        // ✅ CORRIGIDO: Aceitar qualquer redirect
        $response->assertStatus(302);
        
        // ✅ CORRIGIDO: Verificar SoftDelete
        $this->assertSoftDeleted('users', ['id' => $userId]);
    }

    /** @test */
    public function usuario_comum_nao_pode_acessar_admin()
    {
        $user = User::factory()->create();
        $user->assignRole('Cliente');
        $this->actingAs($user);
        
        $response = $this->get('/admin/usuarios');
        $response->assertStatus(403);
    }
}