<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProdutoCreationTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_pode_criar_um_produto_com_sucesso()
    {
        // 1. Cria um usuário Admin e faz login
        $admin = User::factory()->create();
        $admin->assignRole('Admin'); // Assumindo que você usa Spatie Permission
        $this->actingAs($admin);

        // 2. Dados válidos para o formulário
        $dadosProduto = [
            'descricao' => 'Resistor 10k 1/4W Teste Automatizado',
            'referencia' => 'RES-TESTE-AUTO-01',
            'categoria' => 'COMPONENTES',
            'valor_compra' => '1.00',
            'margem_lucro' => '80', // Dentro da regra 60-150
            'ipi' => '9.75',
            'quantidade' => '100',
            'estoque_minimo' => '10',
            'ativo' => '1',
        ];

        // 3. Faz a requisição POST para a rota de criação
        // Ajuste 'admin.produtos.store' para o nome real da sua rota
        $response = $this->post(route('admin.produtos.store'), $dadosProduto);

        // 4. Assertivas (Verificações)
        $response->assertRedirect(route('admin.produtos.index'));
        $response->assertSessionHas('success', 'Produto criado com sucesso!');

        // 5. Verifica no banco de dados se os cálculos foram feitos pela Action/Domain
        $this->assertDatabaseHas('produtos', [
            'referencia' => 'RES-TESTE-AUTO-01',
            'valor_compra' => 1.00,
            'valor_atacado' => 5.00, // 1.00 / (1 - 0.80) = 5.00
            'percentual_custo' => 20.00,
        ]);
    }

    public function test_sistema_rejeita_margem_de_lucro_invalida()
    {
        $admin = User::factory()->create();
        $admin->assignRole('Admin');
        $this->actingAs($admin);

        $dadosInvalidos = [
            'descricao' => 'Produto Inválido',
            'referencia' => 'RES-INVALIDO',
            'valor_compra' => '1.00',
            'margem_lucro' => '40', // ❌ Menor que 60% (Regra de Domínio)
            'ipi' => '0',
            'quantidade' => '10',
        ];

        $response = $this->post(route('admin.produtos.store'), $dadosInvalidos);

        // O FormRequest ou a Action deve bloquear e retornar erro
        $response->assertSessionHasErrors(['margem_lucro']); 
        // OU, se a validação for na Action, assertStatus(500) ou assertSessionHas('error')
    }
}