<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function usuario_pode_ver_pagina_de_login()
    {
        $response = $this->get('/login');
        
        $response->assertStatus(200);
        $response->assertViewIs('auth.login');
    }

    /** @test */
    public function usuario_pode_ver_pagina_de_registro()
    {
        $response = $this->get('/register');
        
        $response->assertStatus(200);
        $response->assertViewIs('auth.register');
    }

    /** @test */
    public function usuario_pode_fazer_login()
    {
        $user = User::factory()->create([
            'password' => bcrypt('password123')
        ]);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password123',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($user);
    }

    /** @test */
    public function usuario_nao_pode_fazer_login_com_credenciais_invalidas()
    {
        $user = User::factory()->create();

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'senha_errada',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    /** @test */
    public function usuario_pode_fazer_logout()
    {
        $user = User::factory()->create();
        
        $response = $this->actingAs($user)->post('/logout');
        
        $response->assertRedirect('/');
        $this->assertGuest();
    }

    /** @test */
    public function usuario_pode_se_registrar()
    {
        $response = $this->post('/register', [
            'name' => 'Novo Usuário',
            'email' => 'novo@teste.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertDatabaseHas('users', [
            'email' => 'novo@teste.com',
            'name' => 'Novo Usuário',
        ]);
    }

    /** @test */
    public function usuario_nao_pode_se_registrar_com_email_duplicado()
    {
        User::factory()->create(['email' => 'existente@teste.com']);

        $response = $this->post('/register', [
            'name' => 'Outro Usuário',
            'email' => 'existente@teste.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertSessionHasErrors('email');
    }

    /** @test */
    public function usuario_autenticado_nao_pode_ver_pagina_de_login()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/login');
        
        $response->assertRedirect('/dashboard');
    }

    /** @test */
    public function usuario_nao_autenticado_nao_pode_acessar_pagina_protegida()
    {
        $response = $this->get('/dashboard');
        
        $response->assertRedirect('/login');
    }
}
