<?php

namespace Tests\Unit\Services;

use App\Exceptions\OutOfStockException;
use App\Models\Produto;
use App\Services\StockService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class StockServiceTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected StockService $stockService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->stockService = new StockService();
    }

    /** @test */
    public function pode_validar_estoque_quando_suficiente()
    {
        $produto = Produto::factory()->create(['quantidade' => 10]);
        
        $resultado = $this->stockService->validateStock($produto, 5);
        
        $this->assertTrue($resultado);
    }

    /** @test */
    public function lanca_exception_quando_estoque_insuficiente()
    {
        $this->expectException(OutOfStockException::class);
        $this->expectExceptionMessage('não tem estoque suficiente');
        
        $produto = Produto::factory()->create(['quantidade' => 3]);
        $this->stockService->validateStock($produto, 5);
    }

    /** @test */
    public function pode_reservar_estoque()
    {
        $produto = Produto::factory()->create(['quantidade' => 10]);
        
        $resultado = $this->stockService->reserveStock($produto, 3);
        
        $this->assertTrue($resultado);
        $this->assertEquals(7, $produto->fresh()->quantidade);
    }

    /** @test */
    public function pode_liberar_estoque()
    {
        $produto = Produto::factory()->create(['quantidade' => 5]);
        
        $resultado = $this->stockService->releaseStock($produto, 3);
        
        $this->assertTrue($resultado);
        $this->assertEquals(8, $produto->fresh()->quantidade);
    }

    /** @test */
    public function retorna_false_ao_reservar_sem_estoque_sem_exception()
    {
        $produto = Produto::factory()->create(['quantidade' => 2]);
        
        $resultado = $this->stockService->reserveStock($produto, 5, false);
        
        $this->assertFalse($resultado);
        $this->assertEquals(2, $produto->fresh()->quantidade);
    }

    /** @test */
    public function pode_buscar_produtos_com_estoque_baixo()
    {
        Produto::factory()->create(['quantidade' => 3, 'ativo' => true]);
        Produto::factory()->create(['quantidade' => 10, 'ativo' => true]);
        Produto::factory()->create(['quantidade' => 1, 'ativo' => true]);
        
        $estoqueBaixo = $this->stockService->getLowStockProducts(5);
        
        $this->assertCount(2, $estoqueBaixo);
    }

    /** @test */
    public function pode_buscar_produtos_com_estoque_critico()
    {
        Produto::factory()->create(['quantidade' => 0, 'ativo' => true]);
        Produto::factory()->create(['quantidade' => -1, 'ativo' => true]);
        Produto::factory()->create(['quantidade' => 5, 'ativo' => true]);
        
        $criticos = $this->stockService->getCriticalStockProducts();
        
        $this->assertCount(2, $criticos);
    }

    /** @test */
    public function nao_reserva_estoque_quando_quantidade_zero()
    {
        $produto = Produto::factory()->create(['quantidade' => 10]);
        
        $resultado = $this->stockService->reserveStock($produto, 0);
        
        $this->assertTrue($resultado);
        $this->assertEquals(10, $produto->fresh()->quantidade);
    }

    /** @test */
    public function pode_validar_multiplos_produtos_em_lote()
    {
        $produto1 = Produto::factory()->create(['quantidade' => 10]);
        $produto2 = Produto::factory()->create(['quantidade' => 2]);
        $produto3 = Produto::factory()->create(['quantidade' => 5]);
        
        $items = [
            ['produto_id' => $produto1->id, 'quantidade' => 5],
            ['produto_id' => $produto2->id, 'quantidade' => 3],
            ['produto_id' => $produto3->id, 'quantidade' => 2],
        ];
        
        $errors = $this->stockService->validateMultipleStock($items);
        
        $this->assertCount(1, $errors);
        $this->assertStringContainsString($produto2->descricao, $errors[0]);
    }

    /** @test */
    public function retorna_erro_quando_produto_nao_encontrado_na_validacao_em_lote()
    {
        $items = [
            ['produto_id' => 999, 'quantidade' => 5],
        ];
        
        $errors = $this->stockService->validateMultipleStock($items);
        
        $this->assertCount(1, $errors);
        $this->assertStringContainsString('não encontrado', $errors[0]);
    }
}