<?php

namespace Tests\Feature\Admin;

use Tests\TestCase;
use App\Models\User;
use App\Models\Produto;
use App\Models\Categoria;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ProdutoAdminTest extends TestCase
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

    private function criarUsuarioComum(): User
    {
        $user = User::factory()->create(['ativo' => true]);
        $user->assignRole('Cliente');
        return $user;
    }

    public function test_admin_pode_listar_produtos()
    {
        $admin = $this->criarAdmin();
        $this->actingAs($admin);
        
        Produto::factory()->count(5)->create();
        
        $response = $this->get('/admin/produtos');
        $response->assertStatus(200);
        $response->assertViewHas('produtos');
    }

    public function test_admin_pode_criar_produto()
    {
        $admin = $this->criarAdmin();
        $this->actingAs($admin);
        
        $categoria = Categoria::factory()->create();
        
        $response = $this->post('/admin/produtos', [
            'categoria_id' => $categoria->id,
            'categoria' => $categoria->nome,
            'referencia' => 'REF-' . uniqid(),
            'descricao' => 'Produto Teste Admin',
            'tipo' => 'UNI',
            'disponibilidade' => 'DISPONIVEL',
            'quantidade' => 10,
            'estoque_minimo' => 5,
            'valor_compra' => 50.00,
            'valor_unitario' => 99.99,
            'valor_atacado' => 99.99,
            'ativo' => true,
            'margem_lucro' => 80,
        ]);
        
        // ✅ Verifica redirecionamento
        $response->assertStatus(302);
        $response->assertRedirect('/admin/produtos');
        
        // ✅ Buscar o produto criado
        $produto = Produto::where('descricao', 'Produto Teste Admin')->first();
        $this->assertNotNull($produto, 'Produto não foi criado');
        
        // ✅ CORRIGIDO: O sistema multiplica o valor por 100 (centavos)
        // O valor salvo é 9999 (representando 99.99)
        $this->assertEquals(9999, (int) $produto->valor_unitario);
        // Ou verifica o valor esperado dividido por 100
        $this->assertEquals(99.99, (float) $produto->valor_unitario / 100);
        
        // ✅ Verifica outros campos
        $this->assertEquals($categoria->id, $produto->categoria_id);
        $this->assertEquals(10, $produto->quantidade);
        $this->assertEquals(80, $produto->margem_lucro);
    }

    public function test_usuario_comum_nao_pode_acessar_admin()
    {
        $user = $this->criarUsuarioComum();
        $this->actingAs($user);
        
        $response = $this->get('/admin/produtos');
        $response->assertStatus(403);
    }
}