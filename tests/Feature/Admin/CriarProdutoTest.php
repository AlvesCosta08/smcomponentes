<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class CriarProdutoTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Cria a role de Admin e um usuário para o teste
        $role = Role::firstOrCreate(['name' => 'Admin']);
        $this->admin = User::factory()->create();
        $this->admin->assignRole($role);
    }

    public function test_admin_pode_criar_um_produto_com_sucesso(): void
    {
        $dadosProduto = [
            'descricao' => 'Resistor 10k 1/4W (Teste Automatizado)',
            'referencia' => 'RES-TESTE-AUTO-01',
            'categoria' => 'COMPONENTES',
            'valor_compra' => '1.00',
            'margem_lucro' => '80', // Margem válida
            'ipi' => '9.75',
            'quantidade' => '100',
            'estoque_minimo' => '10',
            'ativo' => '1',
        ];

        $response = $this->actingAs($this->admin)
                         ->post(route('admin.produtos.store'), $dadosProduto);

        // 1. Verifica se redirecionou com sucesso
        $response->assertRedirect(route('admin.produtos.index'));
        $response->assertSessionHas('success', 'Produto criado com sucesso!');

        // 2. Verifica se os dados foram salvos no banco com os cálculos corretos
        $this->assertDatabaseHas('produtos', [
            'referencia' => 'RES-TESTE-AUTO-01',
            'valor_compra' => 1.00,
            'valor_atacado' => 5.00, // 1.00 / (1 - 0.80) = 5.00
            'percentual_custo' => 20.00,
            'ativo' => true,
        ]);
    }

    public function test_sistema_rejeita_criacao_com_margem_invalida(): void
    {
        $dadosInvalidos = [
            'descricao' => 'Produto Inválido',
            'referencia' => 'RES-INVALIDO',
            'categoria' => 'TESTE',
            'valor_compra' => '10.00',
            'margem_lucro' => '40', // ❌ Menor que 60%
            'ipi' => '0',
            'quantidade' => '10',
            'estoque_minimo' => '5',
            'ativo' => '1',
        ];

        $response = $this->actingAs($this->admin)
                         ->post(route('admin.produtos.store'), $dadosInvalidos);

        // Deve falhar na validação do FormRequest ou lançar exceção na Action
        // Se a validação estiver no FormRequest:
        $response->assertSessionHasErrors('margem_lucro');
        
        // Se a validação estiver na Action (via Exception), o Laravel converte para erro 500 ou mensagem customizada.
        // $response->assertStatus(500); 
    }
}