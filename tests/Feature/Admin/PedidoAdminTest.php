<?php

namespace Tests\Feature\Admin;

use Tests\TestCase;
use App\Models\User;
use App\Models\Pedido;
use App\Domain\Pedidos\Enums\StatusPedidoEnum;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PedidoAdminTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->artisan('db:seed', ['--class' => 'RoleSeeder', '--force' => true]);
    }

    private function criarAdmin(): User
    {
        $admin = User::factory()->create([
            'email' => 'admin@teste.com',
            'password' => bcrypt('password123'),
            'ativo' => true,
            'name' => 'Admin Teste',
        ]);
        $admin->assignRole('Admin');
        return $admin;
    }

    /** @test */
    public function admin_pode_listar_pedidos()
    {
        $admin = $this->criarAdmin();
        $this->actingAs($admin);
        
        Pedido::factory()->count(3)->create();
        
        $response = $this->get('/admin/pedidos');
        $response->assertStatus(200);
        $response->assertViewHas('pedidos');
    }

    /** @test */
    public function admin_pode_atualizar_status_pedido()
    {
        $admin = $this->criarAdmin();
        $this->actingAs($admin);
        
        $pedido = Pedido::factory()->create(['status' => 'pendente']);
        
        $response = $this->put("/admin/pedidos/{$pedido->id}/status", [
            'status' => 'enviado'
        ]);
        
        // ✅ CORRIGIDO: Aceitar qualquer redirect
        $response->assertStatus(302);
        $this->assertDatabaseHas('pedidos', [
            'id' => $pedido->id,
            'status' => 'enviado'
        ]);
    }
}