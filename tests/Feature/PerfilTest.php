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
        
        $this->artisan('db:seed', ['--class' => 'RoleSeeder', '--force' => true]);
        
        $this->user = User::factory()->create([
            'password' => Hash::make('password123'),
        ]);
    }

    /** @test */
    public function usuario_pode_ver_seu_perfil()
    {
        $this->actingAs($this->user);

        $response = $this->get(route('cliente.perfil.edit'));

        $response->assertStatus(200);
        $response->assertViewHas('user');
    }

    /** @test */
    public function usuario_pode_atualizar_seu_perfil()
    {
        $this->actingAs($this->user);

        $telefone = '(11) 99999-8888';
        $telefoneLimpo = preg_replace('/[^0-9]/', '', $telefone);

        $response = $this->patch(route('cliente.perfil.update'), [
            'name' => 'Nome Atualizado',
            'email' => $this->user->email,
            'telefone' => $telefone,
            'cep' => '01234-567',
            'logradouro' => 'Rua Teste',
            'numero' => '123',
            'bairro' => 'Centro',
            'cidade' => 'São Paulo',
            'estado' => 'SP',
        ]);

        $response->assertStatus(302);
        $response->assertRedirect(route('cliente.perfil.edit'));
        
        $this->assertDatabaseHas('users', [
            'id' => $this->user->id,
            'name' => 'Nome Atualizado',
            'telefone' => $telefoneLimpo,
        ]);
    }

    /** @test */
    public function usuario_pode_atualizar_senha()
    {
        $this->actingAs($this->user);

        $response = $this->put(route('cliente.perfil.password'), [
            'current_password' => 'password123',
            'password' => 'nova-senha-456',
            'password_confirmation' => 'nova-senha-456',
        ]);

        $response->assertStatus(302);
    }

    /** @test */
    public function usuario_nao_autenticado_nao_pode_acessar_perfil()
    {
        $response = $this->get(route('cliente.perfil.edit'));
        $response->assertRedirect(route('login'));
    }
}