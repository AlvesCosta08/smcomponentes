<?php

namespace Tests\Unit;

use App\Models\Produto;
use App\Models\Categoria;
use App\Enums\DisponibilidadeEnum;
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
            'quantidade' => 10
        ]);

        $this->assertDatabaseHas('produtos', [
            'id' => $produto->id,
            'descricao' => 'Produto Teste'
        ]);
        
        $this->assertNotNull($produto->valor_atacado);
        $this->assertGreaterThan(0, $produto->valor_atacado);
    }

    /** @test */
    public function produto_tem_referencia_unica()
    {
        Produto::factory()->create(['referencia' => 'REF-001']);
        
        $this->expectException(\Illuminate\Database\QueryException::class);
        
        Produto::factory()->create(['referencia' => 'REF-001']);
    }

    /** @test */
    public function produto_calcula_corretamente_valor_atacado()
    {
        $produto = Produto::factory()->create([
            'valor_compra' => 100.00,
            'margem_lucro' => 70,
            'ipi' => 0
        ]);
        
        $this->assertEquals(333.33, $produto->valor_atacado);
        $this->assertEquals(30.00, $produto->percentual_custo);
    }

    /** @test */
    public function produto_verifica_disponibilidade()
    {
        $disponivel = Produto::factory()->disponivel()->create();
        $indisponivel = Produto::factory()->indisponivel()->create();
        $estoqueBaixo = Produto::factory()->estoqueBaixo()->create();

        $this->assertEquals(DisponibilidadeEnum::DISPONIVEL, $disponivel->disponibilidade);
        $this->assertEquals(DisponibilidadeEnum::INDISPONIVEL, $indisponivel->disponibilidade);
        $this->assertEquals(DisponibilidadeEnum::ESTOQUE_BAIXO, $estoqueBaixo->disponibilidade);
    }

    /** @test */
    public function produto_verifica_se_esta_disponivel_para_venda()
    {
        $disponivel = Produto::factory()->disponivel()->create();
        $indisponivel = Produto::factory()->indisponivel()->create();

        $this->assertTrue($disponivel->ativo);
        $this->assertFalse($indisponivel->ativo);
    }

    /** @test */
    public function produto_pode_ser_ativado_ou_desativado()
    {
        $produtoAtivo = Produto::factory()->create(['ativo' => true]);
        $produtoInativo = Produto::factory()->inativo()->create();
        
        $this->assertTrue($produtoAtivo->ativo);
        $this->assertFalse($produtoInativo->ativo);
    }

    /** @test */
    public function produto_pode_ser_destaque()
    {
        $produtoDestaque = Produto::factory()->destaque()->create();
        $produtoNormal = Produto::factory()->create(['destaque' => false]);
        
        $this->assertTrue($produtoDestaque->destaque);
        $this->assertFalse($produtoNormal->destaque);
    }

    /** @test */
    public function produto_pode_ser_novo()
    {
        $produtoNovo = Produto::factory()->novo()->create();
        $produtoNormal = Produto::factory()->create(['novo' => false]);
        
        $this->assertTrue($produtoNovo->novo);
        $this->assertFalse($produtoNormal->novo);
    }

    /** @test */
    public function produto_tem_preco_promocional()
    {
        $produto = Produto::factory()->comPromocao()->create();
        
        $this->assertNotNull($produto->preco_promocional);
        $this->assertLessThan($produto->valor_atacado, $produto->preco_promocional);
    }

    /** @test */
    public function produto_pertence_a_categoria()
    {
        // Criar categoria
        $categoria = Categoria::factory()->create([
            'nome' => 'Categoria Teste'
        ]);
        
        // Criar produto com categoria_id
        $produto = Produto::factory()->create([
            'categoria_id' => $categoria->id
        ]);
        
        // Recarregar do banco
        $produto->refresh();
        
        // Verificar se o categoria_id foi salvo
        $this->assertEquals($categoria->id, $produto->categoria_id);
        
        // ✅ CORREÇÃO: Usar o método de relacionamento com first()
        $categoriaRelacionada = $produto->categoria()->first();
        
        // Verificar se o relacionamento funciona
        $this->assertNotNull($categoriaRelacionada, 'Categoria relacionada não encontrada');
        $this->assertInstanceOf(Categoria::class, $categoriaRelacionada);
        $this->assertEquals($categoria->id, $categoriaRelacionada->id);
    }

    /** @test */
    public function produto_com_quantidade_especifica()
    {
        $produto = Produto::factory()->comQuantidade(25)->create();
        
        $this->assertEquals(25, $produto->quantidade);
        $this->assertEquals(DisponibilidadeEnum::DISPONIVEL, $produto->disponibilidade);
        $this->assertTrue($produto->ativo);
    }

    /** @test */
    public function produto_com_quantidade_zero_fica_indisponivel()
    {
        $produto = Produto::factory()->comQuantidade(0)->create();
        
        $this->assertEquals(0, $produto->quantidade);
        $this->assertEquals(DisponibilidadeEnum::INDISPONIVEL, $produto->disponibilidade);
        $this->assertFalse($produto->ativo);
    }

    /** @test */
    public function produto_calcula_percentual_custo_corretamente()
    {
        $produto = Produto::factory()->create([
            'valor_compra' => 50.00,
            'margem_lucro' => 80,
            'ipi' => 0
        ]);
        
        $this->assertEquals(250.00, $produto->valor_atacado);
        $this->assertEquals(20.00, $produto->percentual_custo);
    }

    /** @test */
    public function produto_deve_recalcular_precos_ao_atualizar_margem()
    {
        $produto = Produto::factory()->create([
            'valor_compra' => 50.00,
            'margem_lucro' => 80,
            'ipi' => 0
        ]);
        
        $this->assertEquals(250.00, $produto->valor_atacado);
        $this->assertEquals(20.00, $produto->percentual_custo);
        
        $produto->update([
            'margem_lucro' => 70,
            'valor_compra' => 50.00
        ]);
        
        $produto->refresh();
        
        $this->assertEquals(166.67, $produto->valor_atacado);
        $this->assertEquals(30.00, $produto->percentual_custo);
    }
}