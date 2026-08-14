<?php

namespace Tests\Unit\Services;

use App\DTOs\ProductDTO;
use App\Models\Produto;
use App\Repositories\ProdutoRepository;
use App\Services\ProductService;
use App\Services\StockService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class ProductServiceTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected ProductService $productService;

    protected function setUp(): void
    {
        parent::setUp();
        $repository = new ProdutoRepository();
        $stockService = new StockService();
        $this->productService = new ProductService($repository, $stockService);
    }

    /** @test */
    public function pode_listar_produtos()
    {
        Produto::factory()->count(5)->create(['ativo' => true]);
        
        $resultado = $this->productService->listProducts([], 10);
        
        $this->assertEquals(5, $resultado->total());
    }

    /** @test */
    public function pode_listar_produtos_com_filtros()
    {
        Produto::factory()->create(['categoria' => 'Eletrônicos', 'ativo' => true]);
        Produto::factory()->create(['categoria' => 'Roupas', 'ativo' => true]);
        
        $resultado = $this->productService->listProducts(['categoria' => 'Eletrônicos'], 10);
        
        $this->assertEquals(1, $resultado->total());
    }

    /** @test */
    public function pode_listar_apenas_produtos_ativos()
    {
        Produto::factory()->create(['ativo' => true]);
        Produto::factory()->create(['ativo' => false]);
        
        $resultado = $this->productService->listProducts(['ativo' => true], 10);
        
        $this->assertEquals(1, $resultado->total());
    }

    /** @test */
    public function pode_buscar_produto_por_id()
    {
        $produto = Produto::factory()->create();
        
        $resultado = $this->productService->getProductById($produto->id);
        
        $this->assertNotNull($resultado);
        $this->assertEquals($produto->id, $resultado->id);
        $this->assertEquals($produto->descricao, $resultado->descricao);
    }

    /** @test */
    public function retorna_null_quando_produto_nao_encontrado_por_id()
    {
        $resultado = $this->productService->getProductById(999);
        
        $this->assertNull($resultado);
    }

    /** @test */
    public function pode_buscar_produto_por_slug()
    {
        $produto = Produto::factory()->create(['slug' => 'produto-teste']);
        
        $resultado = $this->productService->getProductBySlug('produto-teste');
        
        $this->assertNotNull($resultado);
        $this->assertEquals($produto->id, $resultado->id);
    }

    /** @test */
    public function pode_buscar_produto_por_referencia()
    {
        $produto = Produto::factory()->create(['referencia' => 'REF-001']);
        
        $resultado = $this->productService->getProductByReference('REF-001');
        
        $this->assertNotNull($resultado);
        $this->assertEquals($produto->id, $resultado->id);
    }

    /** @test */
    public function pode_criar_produto()
    {
        $dto = new ProductDTO(
            id: null,
            descricao: 'Produto Teste',
            categoria: 'Eletrônicos',
            referencia: 'REF-001',
            slug: 'produto-teste',
            tipo: 'unidade',
            disponibilidade: 'DISPONIVEL',
            imagem: null,
            imagem_file: null,
            quantidade: 10,
            estoque_minimo: 5,
            valor_atacado: null,
            valor_compra: null,
            valor_unitario: 99.90,
            valor_custo: null,
            preco_promocional: null,
            ipi: null,
            percentual_custo: null,
            margem_lucro: null,
            ativo: true,
            destaque: false,
            data_compra: null,
            visualizacoes: 0,
            novo: false,
            mais_vendido: false,
        );

        $resultado = $this->productService->createProduct($dto);
        
        $this->assertNotNull($resultado);
        $this->assertEquals('Produto Teste', $resultado->descricao);
        $this->assertEquals('REF-001', $resultado->referencia);
        $this->assertEquals(1, Produto::count());
    }

    /** @test */
    public function pode_atualizar_produto()
    {
        $produto = Produto::factory()->create(['descricao' => 'Original']);
        
        $dto = new ProductDTO(
            id: $produto->id,
            descricao: 'Atualizado',
            categoria: $produto->categoria,
            referencia: $produto->referencia,
            slug: 'produto-atualizado',
            tipo: $produto->tipo,
            disponibilidade: $produto->disponibilidade,
            imagem: $produto->imagem,
            imagem_file: null,
            quantidade: $produto->quantidade,
            estoque_minimo: $produto->estoque_minimo,
            valor_atacado: $produto->valor_atacado,
            valor_compra: $produto->valor_compra,
            valor_unitario: $produto->valor_unitario,
            valor_custo: $produto->valor_custo,
            preco_promocional: $produto->preco_promocional,
            ipi: $produto->ipi,
            percentual_custo: $produto->percentual_custo,
            margem_lucro: $produto->margem_lucro,
            ativo: $produto->ativo,
            destaque: $produto->destaque,
            data_compra: $produto->data_compra,
            visualizacoes: $produto->visualizacoes ?? 0,
            novo: $produto->novo ?? false,
            mais_vendido: $produto->mais_vendido ?? false,
        );

        $resultado = $this->productService->updateProduct($produto->id, $dto);
        
        $this->assertEquals('Atualizado', $resultado->descricao);
    }

    /** @test */
    public function pode_deletar_produto()
    {
        $produto = Produto::factory()->create();
        
        $resultado = $this->productService->deleteProduct($produto->id);
        
        $this->assertTrue($resultado);
        $this->assertSoftDeleted('produtos', ['id' => $produto->id]);
    }

    /** @test */
    public function pode_restaurar_produto_deletado()
    {
        $produto = Produto::factory()->create();
        $this->productService->deleteProduct($produto->id);
        
        $resultado = $this->productService->restoreProduct($produto->id);
        
        $this->assertTrue($resultado);
        $this->assertDatabaseHas('produtos', ['id' => $produto->id]);
    }

    /** @test */
    public function pode_ajustar_estoque_adicionando()
    {
        $produto = Produto::factory()->create(['quantidade' => 5]);
        
        $resultado = $this->productService->adjustStock($produto->id, 3, 'add');
        
        $this->assertTrue($resultado);
        $this->assertEquals(8, $produto->fresh()->quantidade);
    }

    /** @test */
    public function pode_ajustar_estoque_removendo()
    {
        $produto = Produto::factory()->create(['quantidade' => 10]);
        
        $resultado = $this->productService->adjustStock($produto->id, 3, 'remove');
        
        $this->assertTrue($resultado);
        $this->assertEquals(7, $produto->fresh()->quantidade);
    }

    /** @test */
    public function pode_buscar_produtos_por_categoria()
    {
        Produto::factory()->create(['categoria' => 'Eletrônicos', 'ativo' => true]);
        Produto::factory()->create(['categoria' => 'Roupas', 'ativo' => true]);
        
        $resultado = $this->productService->getByCategoria('Eletrônicos', 10);
        
        $this->assertEquals(1, $resultado->total());
    }

    /** @test */
    public function pode_buscar_produtos_por_termo()
    {
        Produto::factory()->create(['descricao' => 'Notebook Dell', 'ativo' => true]);
        Produto::factory()->create(['descricao' => 'Mouse USB', 'ativo' => true]);
        
        $resultado = $this->productService->search('Notebook', 10);
        
        $this->assertEquals(1, $resultado->total());
    }

    /** @test */
    public function pode_obter_produtos_em_destaque()
    {
        Produto::factory()->count(3)->create(['destaque' => true, 'ativo' => true]);
        Produto::factory()->count(2)->create(['destaque' => false, 'ativo' => true]);
        
        $destaques = $this->productService->getDestaques(10);
        
        $this->assertCount(3, $destaques);
    }

    /** @test */
    public function pode_obter_produtos_em_oferta()
    {
        Produto::factory()->count(2)->create([
            'preco_promocional' => 50.00,
            'valor_unitario' => 100.00,
            'ativo' => true,
        ]);
        Produto::factory()->count(3)->create([
            'preco_promocional' => null,
            'ativo' => true,
        ]);
        
        $ofertas = $this->productService->getOfertas(10);
        
        $this->assertCount(2, $ofertas);
    }

    /** @test */
    public function pode_obter_produtos_novos()
    {
        Produto::factory()->count(2)->create(['novo' => true, 'ativo' => true]);
        Produto::factory()->count(3)->create(['novo' => false, 'ativo' => true]);
        
        $novos = $this->productService->getNovos(10);
        
        $this->assertCount(2, $novos);
    }

    /** @test */
    public function pode_obter_produtos_com_baixo_estoque()
    {
        Produto::factory()->create(['quantidade' => 3, 'ativo' => true]);
        Produto::factory()->create(['quantidade' => 10, 'ativo' => true]);
        Produto::factory()->create(['quantidade' => 1, 'ativo' => true]);
        
        $resultado = $this->productService->getLowStock(10);
        
        $this->assertEquals(2, $resultado->total());
    }

    /** @test */
    public function pode_obter_estatisticas_dos_produtos()
    {
        Produto::factory()->create(['ativo' => true, 'quantidade' => 10, 'disponibilidade' => 'DISPONIVEL']);
        Produto::factory()->create(['ativo' => true, 'quantidade' => 3, 'disponibilidade' => 'EST.BAIXO']);
        Produto::factory()->create(['ativo' => true, 'quantidade' => 0, 'disponibilidade' => 'INDISPONIVEL']);
        Produto::factory()->create(['ativo' => false]);
        
        $stats = $this->productService->getStats();
        
        $this->assertEquals(4, $stats['total']);
        $this->assertEquals(3, $stats['ativos']);
        $this->assertEquals(1, $stats['inativos']);
        $this->assertEquals(1, $stats['disponiveis']);
        $this->assertEquals(1, $stats['estoque_baixo']);
        $this->assertEquals(1, $stats['indisponiveis']);
    }

    /** @test */
    public function pode_listar_categorias()
    {
        Produto::factory()->create(['categoria' => 'Eletrônicos', 'ativo' => true]);
        Produto::factory()->create(['categoria' => 'Roupas', 'ativo' => true]);
        Produto::factory()->create(['categoria' => 'Eletrônicos', 'ativo' => true]);
        
        $categorias = $this->productService->getCategorias();
        
        $this->assertCount(2, $categorias);
        $this->assertContains('Eletrônicos', $categorias);
        $this->assertContains('Roupas', $categorias);
    }

    /** @test */
    public function pode_contar_produtos_por_categoria()
    {
        Produto::factory()->create(['categoria' => 'Eletrônicos', 'ativo' => true]);
        Produto::factory()->create(['categoria' => 'Eletrônicos', 'ativo' => true]);
        Produto::factory()->create(['categoria' => 'Roupas', 'ativo' => true]);
        
        $contagem = $this->productService->countByCategory();
        
        $this->assertCount(2, $contagem);
        $this->assertEquals(2, $contagem[0]['total']);
        $this->assertEquals('Eletrônicos', $contagem[0]['categoria']);
    }

    /** @test */
    public function pode_incrementar_visualizacoes()
    {
        $produto = Produto::factory()->create(['visualizacoes' => 0]);
        
        $this->productService->incrementarVisualizacoes($produto->id);
        
        $this->assertEquals(1, $produto->fresh()->visualizacoes);
    }
}