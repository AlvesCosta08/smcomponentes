<?php

namespace Tests\Unit;

use App\Models\Produto;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Session;
use Tests\TestCase;

class CarrinhoControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected User $user;
    protected Produto $produto;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create();
        $this->actingAs($this->user);
        
        $this->produto = Produto::factory()->disponivel()->create([
            'descricao' => 'Produto Teste',
            'valor_unitario' => 99.90,
            'quantidade' => 10,
            'categoria' => 'Teste',
        ]);

        Session::forget('carrinho');
        
        // Desabilitar throttle para testes
        $this->withoutMiddleware(\Illuminate\Routing\Middleware\ThrottleRequests::class);
    }

    private function criarProdutoDisponivel(array $overrides = []): Produto
    {
        return Produto::factory()->disponivel()->create(array_merge([
            'categoria' => 'Teste',
        ], $overrides));
    }

    /** @test */
    public function pode_adicionar_produto_ao_carrinho()
    {
        $response = $this->postJson('/carrinho/adicionar', [
            'produto_id' => $this->produto->id,
            'quantidade' => 2,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Produto adicionado ao carrinho!'
            ]);

        $carrinho = Session::get('carrinho', []);
        $this->assertCount(1, $carrinho);
        $this->assertEquals($this->produto->id, $carrinho[0]['produto_id']);
        $this->assertEquals(2, $carrinho[0]['quantidade']);
    }

    /** @test */
    public function nao_adiciona_produto_indisponivel()
    {
        $produtoIndisponivel = Produto::factory()->indisponivel()->create([
            'categoria' => 'Teste',
        ]);

        $response = $this->postJson('/carrinho/adicionar', [
            'produto_id' => $produtoIndisponivel->id,
            'quantidade' => 1,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => false,
                'message' => 'Produto indisponível!'
            ]);

        $this->assertEmpty(Session::get('carrinho', []));
    }

    /** @test */
    public function nao_adiciona_produto_com_estoque_insuficiente()
    {
        $produtoComPoucoEstoque = $this->criarProdutoDisponivel([
            'quantidade' => 2,
        ]);

        $response = $this->postJson('/carrinho/adicionar', [
            'produto_id' => $produtoComPoucoEstoque->id,
            'quantidade' => 5,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => false,
            ]);

        $this->assertStringContainsString('Quantidade indisponível', $response->json('message'));
        $this->assertEmpty(Session::get('carrinho', []));
    }

    /** @test */
    public function nao_excede_limite_maximo_de_itens()
    {
        $limite = 50;
        for ($i = 0; $i < $limite + 1; $i++) {
            $produto = $this->criarProdutoDisponivel();
            
            $response = $this->postJson('/carrinho/adicionar', [
                'produto_id' => $produto->id,
                'quantidade' => 1,
            ]);
            
            if ($i < $limite) {
                $response->assertJson(['success' => true]);
            } else {
                $response->assertJson(['success' => false]);
                $this->assertStringContainsString('Carrinho cheio!', $response->json('message'));
            }
        }

        $carrinho = Session::get('carrinho', []);
        $this->assertCount($limite, $carrinho);
    }

    /** @test */
    public function pode_atualizar_quantidade_do_item()
    {
        $this->postJson('/carrinho/adicionar', [
            'produto_id' => $this->produto->id,
            'quantidade' => 2,
        ]);

        $carrinho = Session::get('carrinho', []);
        $this->assertCount(1, $carrinho);

        // Rota PUT conforme route:list
        $response = $this->putJson('/carrinho/atualizar/0', [
            'quantidade' => 5,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Carrinho atualizado!'
            ]);

        $carrinho = Session::get('carrinho', []);
        $this->assertEquals(5, $carrinho[0]['quantidade']);
    }

    /** @test */
    public function nao_atualiza_quantidade_acima_do_estoque()
    {
        $this->postJson('/carrinho/adicionar', [
            'produto_id' => $this->produto->id,
            'quantidade' => 2,
        ]);

        $response = $this->putJson('/carrinho/atualizar/0', [
            'quantidade' => 15,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => false,
            ]);

        $this->assertStringContainsString('Quantidade indisponível', $response->json('message'));
        
        $carrinho = Session::get('carrinho', []);
        $this->assertEquals(2, $carrinho[0]['quantidade']);
    }

    /** @test */
    public function pode_remover_item_do_carrinho()
    {
        $this->postJson('/carrinho/adicionar', [
            'produto_id' => $this->produto->id,
            'quantidade' => 2,
        ]);
        
        $produto2 = $this->criarProdutoDisponivel();
        $this->postJson('/carrinho/adicionar', [
            'produto_id' => $produto2->id,
            'quantidade' => 1,
        ]);

        $carrinho = Session::get('carrinho', []);
        $this->assertCount(2, $carrinho);

        // Rota DELETE conforme route:list
        $response = $this->deleteJson('/carrinho/remover/0');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Item removido do carrinho!'
            ]);

        $carrinho = Session::get('carrinho', []);
        $this->assertCount(1, $carrinho);
        $this->assertEquals($produto2->id, $carrinho[0]['produto_id']);
    }

    /** @test */
    public function pode_limpar_carrinho()
    {
        $this->postJson('/carrinho/adicionar', [
            'produto_id' => $this->produto->id,
            'quantidade' => 2,
        ]);
        
        $produto2 = $this->criarProdutoDisponivel();
        $this->postJson('/carrinho/adicionar', [
            'produto_id' => $produto2->id,
            'quantidade' => 1,
        ]);

        $carrinho = Session::get('carrinho', []);
        $this->assertCount(2, $carrinho);

        // Rota DELETE conforme route:list
        $response = $this->deleteJson('/carrinho/limpar');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Carrinho limpo!'
            ]);

        $this->assertEmpty(Session::get('carrinho', []));
    }

    /** @test */
    public function calcula_total_corretamente()
    {
        $this->postJson('/carrinho/adicionar', [
            'produto_id' => $this->produto->id,
            'quantidade' => 2,
        ]);
        
        $produto2 = $this->criarProdutoDisponivel([
            'valor_unitario' => 50.00,
        ]);
        $this->postJson('/carrinho/adicionar', [
            'produto_id' => $produto2->id,
            'quantidade' => 3,
        ]);

        $response = $this->getJson('/carrinho/total');
        
        $expectedTotal = (99.90 * 2) + (50.00 * 3);
        $response->assertStatus(200)
            ->assertJson([
                'total' => $expectedTotal,
                'total_formatado' => 'R$ 349,80',
                'success' => true
            ]);
    }

    /** @test */
    public function retorna_contagem_correta_do_carrinho()
    {
        $this->postJson('/carrinho/adicionar', [
            'produto_id' => $this->produto->id,
            'quantidade' => 2,
        ]);
        
        $produto2 = $this->criarProdutoDisponivel();
        $this->postJson('/carrinho/adicionar', [
            'produto_id' => $produto2->id,
            'quantidade' => 3,
        ]);

        $response = $this->getJson('/carrinho/count');
        
        $response->assertStatus(200)
            ->assertJson([
                'count' => 5,
                'success' => true
            ]);
    }

    /** @test */
    public function remove_automaticamente_produto_que_nao_existe_mais()
    {
        $this->postJson('/carrinho/adicionar', [
            'produto_id' => $this->produto->id,
            'quantidade' => 2,
        ]);
        
        $this->produto->delete();

        $response = $this->putJson('/carrinho/atualizar/0', [
            'quantidade' => 3,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => false,
                'message' => 'Produto não encontrado!'
            ]);

        $this->assertEmpty(Session::get('carrinho', []));
    }

    /** @test */
    public function nao_adiciona_item_duplicado_soma_quantidade()
    {
        $this->postJson('/carrinho/adicionar', [
            'produto_id' => $this->produto->id,
            'quantidade' => 2,
        ]);
        
        $response = $this->postJson('/carrinho/adicionar', [
            'produto_id' => $this->produto->id,
            'quantidade' => 3,
        ]);
        
        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);

        $carrinho = Session::get('carrinho', []);
        $this->assertCount(1, $carrinho);
        $this->assertEquals(5, $carrinho[0]['quantidade']);
    }
}
