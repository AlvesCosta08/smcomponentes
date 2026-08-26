<?php

namespace Tests\Unit;

use App\Models\User;
use App\Models\Pedido;
use App\Models\Wishlist;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function pode_criar_um_usuario()
    {
        $user = User::factory()->create([
            'name' => 'João Silva',
            'email' => 'joao@teste.com'
        ]);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'João Silva',
            'email' => 'joao@teste.com'
        ]);
    }

    /** @test */
    public function usuario_tem_email_unico()
    {
        User::factory()->create(['email' => 'unico@email.com']);
        
        $this->expectException(\Illuminate\Database\QueryException::class);
        
        User::factory()->create(['email' => 'unico@email.com']);
    }

    /** @test */
    public function usuario_pode_ser_ativado_ou_desativado()
    {
        $userAtivo = User::factory()->create(['ativo' => true]);
        $userInativo = User::factory()->create(['ativo' => false]);
        
        $this->assertTrue($userAtivo->ativo);
        $this->assertFalse($userInativo->ativo);
    }

    /** @test */
    public function usuario_tem_varios_pedidos()
    {
        $user = User::factory()->create();
        Pedido::factory()->count(3)->create(['user_id' => $user->id]);
        
        $this->assertCount(3, $user->pedidos);
        $this->assertInstanceOf(Pedido::class, $user->pedidos->first());
    }

    /** @test */
    public function usuario_tem_wishlist()
    {
        $user = User::factory()->create();
        Wishlist::factory()->create(['user_id' => $user->id]);
        
        $this->assertInstanceOf(Wishlist::class, $user->wishlist);
    }

    /** @test */
    public function usuario_pode_ter_cpf_opcional()
    {
        // Usar CPF válido (apenas números, formato válido)
        $cpfValido = '52998224725'; // CPF válido para testes
        
        $userComCpf = User::factory()->create(['cpf' => $cpfValido]);
        $this->assertEquals($cpfValido, $userComCpf->cpf);
    }
}