<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PerfilTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create([
            'password' => Hash::make('password123'),
        ]);
    }

    /** @test */
    public function usuario_pode_ver_seu_perfil()
    {
        $this->actingAs($this->user);

        $response = $this->get('/cliente/perfil');

        $response->assertStatus(200);
        $response->assertViewHas('user');
    }

    /** @test */
    public function usuario_pode_atualizar_seu_perfil()
    {
        $this->actingAs($this->user);

        $response = $this->patch('/cliente/perfil', [
            'name' => 'Nome Atualizado',
            'telefone' => '(11) 99999-8888',
        ]);

        $response->assertStatus(302);
        $this->assertDatabaseHas('users', [
            'id' => $this->user->id,
            'name' => 'Nome Atualizado',
        ]);
    }

    /** @test */
    public function usuario_pode_atualizar_senha()
    {
        $this->actingAs($this->user);

        $response = $this->put('/cliente/perfil/senha', [
            'current_password' => 'password123',
            'password' => 'nova-senha-456',
            'password_confirmation' => 'nova-senha-456',
        ]);

        $response->assertStatus(302);
    }

    /** @test */
    public function usuario_nao_autenticado_nao_pode_acessar_perfil()
    {
        $response = $this->get('/cliente/perfil');
        $response->assertRedirect('/login');
    }
}
