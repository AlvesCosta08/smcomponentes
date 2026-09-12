<?php

namespace Tests\Unit;

use App\Models\Produto;
use App\Models\Categoria;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProdutoTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        if (!\Illuminate\Support\Facades\Schema::hasTable('categorias')) {
            $this->markTestSkipped('Tabela categorias não existe');
        }
    }

    /** @test */
    public function pode_criar_um_produto()
    {
        $produto = Produto::factory()->create([
            'descricao' => 'Produto Teste',
            'quantidade' => 10,
            'valor_unitario' => 99.90,
            'status' => 'disponivel'
        ]);

        $this->assertDatabaseHas('produtos', [
            'id' => $produto->id,
            'descricao' => 'Produto Teste',
            'valor_unitario' => 99.90,
            'status' => 'disponivel'
        ]);
    }

    /** @test */
    public function produto_tem_referencia_unica()
    {
        Produto::factory()->create(['referencia' => 'REF-001']);
        
        $this->expectException(\Illuminate\Database\QueryException::class);
        
        Produto::factory()->create(['referencia' => 'REF-001']);
    }

    /** @test */
    public function produto_verifica_status()
    {
        $disponivel = Produto::factory()->create(['status' => 'disponivel', 'quantidade' => 10, 'ativo' => true]);
        $indisponivel = Produto::factory()->create(['status' => 'indisponivel', 'quantidade' => 0, 'ativo' => true]);
        $sobEncomenda = Produto::factory()->create(['status' => 'sob_encomenda', 'quantidade' => 5, 'ativo' => true]);

        $this->assertEquals('disponivel', $disponivel->status);
        $this->assertEquals('indisponivel', $indisponivel->status);
        $this->assertEquals('sob_encomenda', $sobEncomenda->status);
    }

    /** @test */
    public function produto_verifica_se_esta_disponivel_para_venda()
    {
        $disponivel = Produto::factory()->create(['status' => 'disponivel', 'quantidade' => 10, 'ativo' => true]);
        $indisponivel = Produto::factory()->create(['status' => 'indisponivel', 'quantidade' => 0, 'ativo' => true]);
        $inativo = Produto::factory()->create(['status' => 'disponivel', 'quantidade' => 10, 'ativo' => false]);

        $this->assertTrue($disponivel->disponivel);
        $this->assertFalse($indisponivel->disponivel);
        $this->assertFalse($inativo->disponivel);
    }

    /** @test */
    public function produto_pode_ser_ativado_ou_desativado()
    {
        $produtoAtivo = Produto::factory()->create(['ativo' => true]);
        $produtoInativo = Produto::factory()->create(['ativo' => false]);
        
        $this->assertTrue($produtoAtivo->ativo);
        $this->assertFalse($produtoInativo->ativo);
    }

    /** @test */
    public function produto_pode_ser_destaque()
    {
        $produtoDestaque = Produto::factory()->create(['destaque' => true]);
        $produtoNormal = Produto::factory()->create(['destaque' => false]);
        
        $this->assertTrue($produtoDestaque->destaque);
        $this->assertFalse($produtoNormal->destaque);
    }

    /** @test */
    public function produto_pode_ser_novo()
    {
        $produtoNovo = Produto::factory()->create(['novo' => true]);
        $produtoNormal = Produto::factory()->create(['novo' => false]);
        
        $this->assertTrue($produtoNovo->novo);
        $this->assertFalse($produtoNormal->novo);
    }

    /** @test */
    public function produto_tem_preco_promocional()
    {
        $produto = Produto::factory()->create([
            'valor_atacado' => 100.00,
            'valor_unitario' => 100.00,
            'preco_promocional' => 79.90
        ]);
        
        $this->assertNotNull($produto->preco_promocional);
        $this->assertLessThan($produto->valor_unitario, $produto->preco_promocional);
        $this->assertTrue($produto->tem_promocao);
    }

    /** @test */
    public function produto_pertence_a_categoria()
    {
        $categoria = Categoria::factory()->create([
            'nome' => 'Categoria Teste'
        ]);
        
        $produto = Produto::factory()->create([
            'categoria_id' => $categoria->id
        ]);
        
        $produto->refresh();
        
        $this->assertEquals($categoria->id, $produto->categoria_id);
        $this->assertNotNull($produto->categoria);
        $this->assertInstanceOf(Categoria::class, $produto->categoria);
        $this->assertEquals($categoria->id, $produto->categoria->id);
    }

    /** @test */
    public function produto_com_quantidade_especifica()
    {
        $produto = Produto::factory()->create([
            'quantidade' => 25,
            'status' => 'disponivel',
            'ativo' => true
        ]);
        
        $this->assertEquals(25, $produto->quantidade);
        $this->assertEquals('disponivel', $produto->status);
        $this->assertTrue($produto->ativo);
    }

    /** @test */
    public function produto_com_quantidade_zero_fica_indisponivel_ao_atualizar()
    {
        $produto = Produto::factory()->create([
            'quantidade' => 10,
            'status' => 'disponivel',
            'ativo' => true
        ]);
        
        // Simula atualização de estoque para zero
        $produto->quantidade = 0;
        $produto->atualizarDisponibilidade();
        $produto->save();
        
        $produto->refresh();
        
        $this->assertEquals(0, $produto->quantidade);
        $this->assertEquals('indisponivel', $produto->status);
        $this->assertTrue($produto->ativo); // ativo continua true, mas status muda
    }

    /** @test */
    public function produto_gera_slug_automaticamente_ao_criar()
    {
        $produto = Produto::factory()->create([
            'descricao' => 'Produto Teste Slug',
            'slug' => null,
            'referencia' => 'REF-001'
        ]);
        
        $this->assertNotNull($produto->slug);
        $this->assertEquals('produto-teste-slug', $produto->slug);
    }

    /** @test */
    public function produto_nao_permite_slug_duplicado()
    {
        $produto1 = Produto::factory()->create([
            'descricao' => 'Mesmo Slug',
            'slug' => 'slug-unico'
        ]);
        
        $produto2 = Produto::factory()->create([
            'descricao' => 'Mesmo Slug Outro',
            'slug' => null,
        ]);
        
        // O segundo deve ter slug diferente
        $this->assertNotEquals($produto1->slug, $produto2->slug);
        $this->assertEquals('mesmo-slug-outro', $produto2->slug);
    }

    /** @test */
    public function produto_incrementa_visualizacoes()
    {
        $produto = Produto::factory()->create(['visualizacoes' => 0]);
        
        $produto->incrementarVisualizacoes();
        $produto->refresh();
        
        $this->assertEquals(1, $produto->visualizacoes);
        $this->assertNotNull($produto->ultima_visualizacao);
    }

    /** @test */
    public function produto_pode_aumentar_estoque()
    {
        $produto = Produto::factory()->create(['quantidade' => 5, 'status' => 'disponivel']);
        
        $produto->aumentarEstoque(10);
        $produto->refresh();
        
        $this->assertEquals(15, $produto->quantidade);
        $this->assertNotNull($produto->ultima_atualizacao_estoque);
    }

    /** @test */
    public function produto_pode_reduzir_estoque()
    {
        $produto = Produto::factory()->create(['quantidade' => 10, 'status' => 'disponivel']);
        
        $produto->reduzirEstoque(3);
        $produto->refresh();
        
        $this->assertEquals(7, $produto->quantidade);
        $this->assertNotNull($produto->ultima_atualizacao_estoque);
    }

    /** @test */
    public function produto_nao_pode_reduzir_estoque_acima_do_disponivel()
    {
        $produto = Produto::factory()->create(['quantidade' => 5]);
        
        $resultado = $produto->reduzirEstoque(10);
        
        $this->assertFalse($resultado);
        $this->assertEquals(5, $produto->quantidade);
    }

    /** @test */
    public function produto_verifica_se_tem_estoque_suficiente()
    {
        $produto = Produto::factory()->create(['quantidade' => 5]);
        
        $this->assertTrue($produto->temEstoque(3));
        $this->assertTrue($produto->temEstoque(5));
        $this->assertFalse($produto->temEstoque(6));
    }

    /** @test */
    public function produto_retorna_preco_formatado()
    {
        $produto = Produto::factory()->create(['valor_unitario' => 199.90]);
        
        $this->assertEquals('R$ 199,90', $produto->preco_formatado);
    }

    /** @test */
    public function produto_retorna_preco_promocional_formatado()
    {
        $produto = Produto::factory()->create([
            'valor_unitario' => 199.90,
            'preco_promocional' => 149.90
        ]);
        
        $this->assertEquals('R$ 149,90', $produto->preco_promocional_formatado);
    }

    /** @test */
    public function produto_retorna_status_label()
    {
        $disponivel = Produto::factory()->create(['status' => 'disponivel', 'ativo' => true]);
        $indisponivel = Produto::factory()->create(['status' => 'indisponivel', 'ativo' => true]);
        $sobEncomenda = Produto::factory()->create(['status' => 'sob_encomenda', 'ativo' => true]);
        $inativo = Produto::factory()->create(['status' => 'disponivel', 'ativo' => false]);

        $this->assertEquals('Disponível', $disponivel->status_label);
        $this->assertEquals('Indisponível', $indisponivel->status_label);
        $this->assertEquals('Sob Encomenda', $sobEncomenda->status_label);
        $this->assertEquals('Inativo', $inativo->status_label);
    }

    /** @test */
    public function produto_retorna_desconto_percentual()
    {
        $produto = Produto::factory()->create([
            'valor_atacado' => 100.00,
            'valor_unitario' => 100.00,
            'preco_promocional' => 79.90
        ]);
        
        $this->assertEquals(20, $produto->desconto_percentual);
    }

    /** @test */
    public function produto_sem_promocao_retorna_desconto_zero()
    {
        $produto = Produto::factory()->create([
            'valor_atacado' => 100.00,
            'valor_unitario' => 100.00,
            'preco_promocional' => null
        ]);
        
        $this->assertEquals(0, $produto->desconto_percentual);
        $this->assertFalse($produto->tem_promocao);
    }
}