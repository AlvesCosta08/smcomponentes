<?php

namespace Tests\Unit\Services;

use App\Models\Produto;
use App\Repositories\Contracts\ProdutoRepositoryInterface;
use App\Services\StockService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StockServiceTest extends TestCase
{
    use RefreshDatabase;

    protected StockService $stockService;
    protected $produtoRepository;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->produtoRepository = app(ProdutoRepositoryInterface::class);
        $this->stockService = new StockService($this->produtoRepository);
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
        $this->expectException(\App\Exceptions\OutOfStockException::class);
        
        $produto = Produto::factory()->create(['quantidade' => 2]);
        
        $this->stockService->reserveStock($produto, 5);
    }

    // ... outros testes
}