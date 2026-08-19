<?php

namespace Tests\Unit\Services;

use App\DTOs\ProductDTO;
use App\Models\Produto;
use App\Repositories\ProdutoRepository;
use App\Services\ProductService;
use App\Services\StockService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductServiceTest extends TestCase
{
    use RefreshDatabase;

    protected ProductService $productService;
    protected ProdutoRepository $repository;
    protected StockService $stockService;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->repository = new ProdutoRepository();
        $this->stockService = new StockService($this->repository);
        $this->productService = new ProductService($this->repository, $this->stockService);
    }

    private function createProductDTO(array $data = []): ProductDTO
    {
        $defaults = [
            'id' => null,
            'descricao' => 'Produto Teste',
            'categoria' => 'Categoria Teste',
            'referencia' => 'REF-' . rand(100, 999),
            'slug' => 'produto-teste',
            'tipo' => 'Produto',
            'disponibilidade' => Produto::DISPONIVEL,
            'imagem' => null,
            'imagem_file' => null,
            'quantidade' => 10,
            'estoque_minimo' => 5,
            'valor_atacado' => null,
            'valor_compra' => null,
            'valor_unitario' => 99.90,
            'valor_custo' => null,
            'preco_promocional' => null,
            'ipi' => 9.75,
            'percentual_custo' => null,
            'margem_lucro' => null,
            'ativo' => true,
            'destaque' => false,
            'data_compra' => null,
            'visualizacoes' => 0,
            'novo' => false,
            'mais_vendido' => false,
            'created_at' => null,
            'updated_at' => null,
        ];

        $merged = array_merge($defaults, $data);

        return new ProductDTO(
            id: $merged['id'],
            descricao: $merged['descricao'],
            categoria: $merged['categoria'],
            referencia: $merged['referencia'],
            slug: $merged['slug'],
            tipo: $merged['tipo'],
            disponibilidade: $merged['disponibilidade'],
            imagem: $merged['imagem'],
            imagem_file: $merged['imagem_file'],
            quantidade: $merged['quantidade'],
            estoque_minimo: $merged['estoque_minimo'],
            valor_atacado: $merged['valor_atacado'],
            valor_compra: $merged['valor_compra'],
            valor_unitario: $merged['valor_unitario'],
            valor_custo: $merged['valor_custo'],
            preco_promocional: $merged['preco_promocional'],
            ipi: $merged['ipi'],
            percentual_custo: $merged['percentual_custo'],
            margem_lucro: $merged['margem_lucro'],
            ativo: $merged['ativo'],
            destaque: $merged['destaque'],
            data_compra: $merged['data_compra'],
            visualizacoes: $merged['visualizacoes'],
            novo: $merged['novo'],
            mais_vendido: $merged['mais_vendido'],
            created_at: $merged['created_at'],
            updated_at: $merged['updated_at'],
        );
    }

    /** @test */
    public function pode_listar_produtos()
    {
        // Usar o estado disponivel() da factory
        Produto::factory()->disponivel()->count(5)->create([
            'categoria' => 'Teste',
        ]);

        $produtos = $this->productService->listProducts([], 15);

        $this->assertEquals(5, $produtos->total());
    }

    /** @test */
    public function pode_listar_produtos_com_filtros()
    {
        Produto::factory()->disponivel()->create([
            'categoria' => 'Eletrônicos',
        ]);
        Produto::factory()->disponivel()->create([
            'categoria' => 'Roupas',
        ]);

        $produtos = $this->productService->listProducts(['categoria' => 'Eletrônicos'], 15);

        $this->assertEquals(1, $produtos->total());
    }

    /** @test */
    public function pode_listar_apenas_produtos_ativos()
    {
        Produto::factory()->disponivel()->create([
            'ativo' => true,
            'categoria' => 'Teste',
        ]);
        Produto::factory()->create([
            'ativo' => false,
            'categoria' => 'Teste',
        ]);

        $produtos = $this->productService->listProducts([], 15);

        $this->assertEquals(2, $produtos->total());
    }

    /** @test */
    public function pode_buscar_produto_por_id()
    {
        $produto = Produto::factory()->disponivel()->create([
            'categoria' => 'Teste',
        ]);

        $resultado = $this->productService->getProductById($produto->id);

        $this->assertNotNull($resultado);
        $this->assertEquals($produto->id, $resultado->id);
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
        $produto = Produto::factory()->disponivel()->create([
            'slug' => 'produto-teste',
            'categoria' => 'Teste',
        ]);

        $resultado = $this->productService->getProductBySlug('produto-teste');

        $this->assertNotNull($resultado);
        $this->assertEquals($produto->id, $resultado->id);
    }

    /** @test */
    public function pode_buscar_produto_por_referencia()
    {
        $produto = Produto::factory()->disponivel()->create([
            'referencia' => 'REF-123',
            'categoria' => 'Teste',
        ]);

        $resultado = $this->productService->getProductByReference('REF-123');

        $this->assertNotNull($resultado);
        $this->assertEquals($produto->id, $resultado->id);
    }

    /** @test */
    public function pode_criar_produto()
    {
        $dto = $this->createProductDTO([
            'descricao' => 'Produto Novo',
            'categoria' => 'Eletrônicos',
            'valor_unitario' => 99.90,
            'quantidade' => 10,
            'ativo' => true,
            'referencia' => 'REF-NOVO',
        ]);

        $resultado = $this->productService->createProduct($dto);

        $this->assertNotNull($resultado);
        $this->assertEquals('Produto Novo', $resultado->descricao);
        $this->assertDatabaseHas('produtos', [
            'descricao' => 'Produto Novo',
            'categoria' => 'Eletrônicos',
        ]);
    }

    /** @test */
    public function pode_atualizar_produto()
    {
        $produto = Produto::factory()->disponivel()->create([
            'descricao' => 'Produto Antigo',
            'categoria' => 'Teste',
        ]);

        $dto = $this->createProductDTO([
            'descricao' => 'Produto Atualizado',
            'categoria' => 'Eletrônicos',
            'valor_unitario' => 99.90,
            'quantidade' => 10,
            'ativo' => true,
            'referencia' => $produto->referencia,
        ]);

        $resultado = $this->productService->updateProduct($produto->id, $dto);

        $this->assertEquals('Produto Atualizado', $resultado->descricao);
        $this->assertDatabaseHas('produtos', [
            'id' => $produto->id,
            'descricao' => 'Produto Atualizado',
            'categoria' => 'Eletrônicos',
        ]);
    }

    /** @test */
    public function pode_deletar_produto()
    {
        $produto = Produto::factory()->disponivel()->create([
            'categoria' => 'Teste',
        ]);

        $resultado = $this->productService->deleteProduct($produto->id);

        $this->assertTrue($resultado);
        $this->assertSoftDeleted('produtos', ['id' => $produto->id]);
    }

    /** @test */
    public function pode_restaurar_produto_deletado()
    {
        $produto = Produto::factory()->disponivel()->create([
            'categoria' => 'Teste',
        ]);
        $this->productService->deleteProduct($produto->id);

        $resultado = $this->productService->restoreProduct($produto->id);

        $this->assertTrue($resultado);
        $this->assertDatabaseHas('produtos', ['id' => $produto->id, 'deleted_at' => null]);
    }

    /** @test */
    public function pode_ajustar_estoque_adicionando()
    {
        $produto = Produto::factory()->disponivel()->create([
            'quantidade' => 5,
            'categoria' => 'Teste',
        ]);

        $resultado = $this->productService->adjustStock($produto->id, 3, 'add');

        $this->assertTrue($resultado);
        $this->assertEquals(8, $produto->fresh()->quantidade);
    }

    /** @test */
    public function pode_ajustar_estoque_removendo()
    {
        $produto = Produto::factory()->disponivel()->create([
            'quantidade' => 5,
            'categoria' => 'Teste',
        ]);

        $resultado = $this->productService->adjustStock($produto->id, 3, 'remove');

        $this->assertTrue($resultado);
        $this->assertEquals(2, $produto->fresh()->quantidade);
    }

    /** @test */
    public function pode_buscar_produtos_por_categoria()
    {
        Produto::factory()->disponivel()->create([
            'categoria' => 'Eletrônicos',
        ]);
        Produto::factory()->disponivel()->create([
            'categoria' => 'Roupas',
        ]);

        $produtos = $this->productService->getByCategoria('Eletrônicos', 15);

        $this->assertEquals(1, $produtos->total());
    }

    /** @test */
    public function pode_buscar_produtos_por_termo()
    {
        Produto::factory()->disponivel()->create([
            'descricao' => 'Notebook Dell',
            'categoria' => 'Teste',
        ]);
        Produto::factory()->disponivel()->create([
            'descricao' => 'Mouse Logitech',
            'categoria' => 'Teste',
        ]);

        $produtos = $this->productService->search('Notebook', 15);

        $this->assertEquals(1, $produtos->total());
    }

    /** @test */
    public function pode_obter_produtos_em_destaque()
    {
        // Usar disponivel() para garantir que o scope funcione
        Produto::factory()->disponivel()->create([
            'destaque' => true,
            'categoria' => 'Teste',
        ]);
        Produto::factory()->disponivel()->create([
            'destaque' => false,
            'categoria' => 'Teste',
        ]);

        $produtos = $this->productService->getDestaques(10);

        $this->assertCount(1, $produtos);
    }

    /** @test */
    public function pode_obter_produtos_em_oferta()
    {
        Produto::factory()->disponivel()->create([
            'preco_promocional' => 50.00,
            'valor_unitario' => 100.00,
            'valor_atacado' => 80.00,
            'categoria' => 'Teste',
        ]);
        Produto::factory()->disponivel()->create([
            'preco_promocional' => null,
            'valor_unitario' => 100.00,
            'categoria' => 'Teste',
        ]);

        $produtos = $this->productService->getOfertas(10);

        $this->assertCount(1, $produtos);
    }

    /** @test */
    public function pode_obter_produtos_novos()
    {
        Produto::factory()->disponivel()->create([
            'novo' => true,
            'categoria' => 'Teste',
            'created_at' => now(),
        ]);
        Produto::factory()->disponivel()->create([
            'novo' => false,
            'categoria' => 'Teste',
            'created_at' => now(),
        ]);

        $produtos = $this->productService->getNovos(10);

        $this->assertCount(1, $produtos);
    }

    /** @test */
    public function pode_obter_produtos_com_baixo_estoque()
    {
        Produto::factory()->disponivel()->create([
            'quantidade' => 2,
            'categoria' => 'Teste',
        ]);
        Produto::factory()->disponivel()->create([
            'quantidade' => 20,
            'categoria' => 'Teste',
        ]);

        $produtos = $this->productService->getLowStock(10);

        $this->assertEquals(1, $produtos->total());
    }

    /** @test */
    public function pode_obter_estatisticas_dos_produtos()
    {
        Produto::factory()->disponivel()->create([
            'ativo' => true,
            'destaque' => true,
            'categoria' => 'Teste',
        ]);
        Produto::factory()->create([
            'ativo' => false,
            'quantidade' => 0,
            'disponibilidade' => Produto::INDISPONIVEL,
            'categoria' => 'Teste',
        ]);

        $stats = $this->productService->getStats();

        $this->assertEquals(2, $stats['total']);
        $this->assertEquals(1, $stats['ativos']);
        $this->assertEquals(1, $stats['inativos']);
    }

    /** @test */
    public function pode_listar_categorias()
    {
        Produto::factory()->disponivel()->create([
            'categoria' => 'Eletrônicos',
        ]);
        Produto::factory()->disponivel()->create([
            'categoria' => 'Roupas',
        ]);

        $categorias = $this->productService->getCategorias();

        $this->assertCount(2, $categorias);
        $this->assertContains('Eletrônicos', $categorias);
        $this->assertContains('Roupas', $categorias);
    }

    /** @test */
    public function pode_contar_produtos_por_categoria()
    {
        Produto::factory()->disponivel()->count(3)->create([
            'categoria' => 'Eletrônicos',
        ]);
        Produto::factory()->disponivel()->count(2)->create([
            'categoria' => 'Roupas',
        ]);

        $contagem = $this->productService->countByCategory();

        $this->assertCount(2, $contagem);
    }

    /** @test */
    public function pode_incrementar_visualizacoes()
    {
        $produto = Produto::factory()->disponivel()->create([
            'visualizacoes' => 0,
            'categoria' => 'Teste',
        ]);

        $this->productService->incrementarVisualizacoes($produto->id);

        $this->assertEquals(1, $produto->fresh()->visualizacoes);
    }
}
