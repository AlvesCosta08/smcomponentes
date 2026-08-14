<?php
// tests/Unit/DTOs/ProductDTOTest.php

namespace Tests\Unit\DTOs;

use App\DTOs\ProductDTO;
use App\Models\Produto;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;

class ProductDTOTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_dto_from_request()
    {
        $request = new Request();
        $request->merge([
            'descricao' => 'Produto Teste',
            'categoria' => 'Eletrônicos',
            'referencia' => 'REF-001',
            'valor_unitario' => 99.90,
            'quantidade' => 10,
            'ativo' => true,
            'destaque' => false,
        ]);

        $dto = ProductDTO::fromRequest($request);

        $this->assertEquals('Produto Teste', $dto->descricao);
        $this->assertEquals('REF-001', $dto->referencia);
        $this->assertEquals(99.90, $dto->valor_unitario);
        $this->assertEquals(10, $dto->quantidade);
        $this->assertTrue($dto->ativo);
        $this->assertFalse($dto->destaque);
    }

    public function test_can_create_dto_from_model()
    {
        // Criar produto com Factory (garante dados únicos)
        $produto = Produto::factory()->create([
            'descricao' => 'Produto Model',
            'referencia' => 'REF-' . uniqid(),
            'valor_unitario' => 149.90,
        ]);

        $dto = ProductDTO::fromModel($produto);

        $this->assertEquals($produto->id, $dto->id);
        $this->assertEquals('Produto Model', $dto->descricao);
        $this->assertEquals(149.90, $dto->valor_unitario);
    }

    public function test_dto_has_formatted_prices()
    {
        $dto = new ProductDTO(
            id: 1,
            descricao: 'Teste',
            categoria: 'Teste',
            referencia: 'REF-003',
            slug: 'teste',
            tipo: null,
            disponibilidade: 'DISPONÍVEL',
            imagem: null,
            imagem_file: null,
            quantidade: 10,
            estoque_minimo: 5,
            valor_atacado: null,
            valor_compra: null,
            valor_unitario: 99.90,
            valor_custo: null,
            preco_promocional: 79.90,
            ipi: null,
            percentual_custo: null,
            margem_lucro: null,
            ativo: true,
            destaque: false,
            data_compra: null,
        );

        $this->assertEquals('R$ 99,90', $dto->getPrecoFormatado());
        $this->assertEquals('R$ 79,90', $dto->getPrecoPromocionalFormatado());
        $this->assertTrue($dto->temPromocao());
    }

    public function test_dto_creates_correct_slug_from_request()
    {
        $request = new Request();
        $request->merge([
            'descricao' => 'Produto Com Espaços',
            'categoria' => 'Eletrônicos',
            'referencia' => 'REF-004',
            'valor_unitario' => 99.90,
            'quantidade' => 10,
        ]);

        $dto = ProductDTO::fromRequest($request);

        $this->assertEquals('produto-com-espacos', $dto->slug);
    }

    public function test_dto_converts_boolean_correctly()
    {
        $request = new Request();
        $request->merge([
            'descricao' => 'Produto Teste',
            'categoria' => 'Eletrônicos',
            'referencia' => 'REF-005',
            'valor_unitario' => 99.90,
            'quantidade' => 10,
            'ativo' => 'true',
            'destaque' => 'false',
            'novo' => '1',
            'mais_vendido' => '0',
        ]);

        $dto = ProductDTO::fromRequest($request);

        $this->assertTrue($dto->ativo);
        $this->assertFalse($dto->destaque);
        $this->assertTrue($dto->novo);
        $this->assertFalse($dto->mais_vendido);
    }

    public function test_dto_handles_null_values_correctly()
    {
        $request = new Request();
        $request->merge([
            'descricao' => 'Produto Teste',
            'categoria' => 'Eletrônicos',
            'referencia' => 'REF-006',
            'valor_unitario' => 99.90,
            'quantidade' => 10,
        ]);

        $dto = ProductDTO::fromRequest($request);

        $this->assertNull($dto->preco_promocional);
        $this->assertNull($dto->tipo);
        $this->assertNull($dto->imagem);
        $this->assertFalse($dto->destaque);
        $this->assertFalse($dto->novo);
        $this->assertFalse($dto->mais_vendido);
    }

    public function test_dto_calculates_promotion_correctly()
    {
        // Com promoção
        $dtoComPromocao = new ProductDTO(
            id: 1,
            descricao: 'Teste',
            categoria: 'Teste',
            referencia: 'REF-007',
            slug: 'teste',
            tipo: null,
            disponibilidade: 'DISPONÍVEL',
            imagem: null,
            imagem_file: null,
            quantidade: 10,
            estoque_minimo: 5,
            valor_atacado: null,
            valor_compra: null,
            valor_unitario: 100.00,
            valor_custo: null,
            preco_promocional: 80.00,
            ipi: null,
            percentual_custo: null,
            margem_lucro: null,
            ativo: true,
            destaque: false,
            data_compra: null,
        );

        $this->assertTrue($dtoComPromocao->temPromocao());
        $this->assertEquals('R$ 100,00', $dtoComPromocao->getPrecoFormatado());
        $this->assertEquals('R$ 80,00', $dtoComPromocao->getPrecoPromocionalFormatado());

        // Sem promoção
        $dtoSemPromocao = new ProductDTO(
            id: 2,
            descricao: 'Teste 2',
            categoria: 'Teste',
            referencia: 'REF-008',
            slug: 'teste-2',
            tipo: null,
            disponibilidade: 'DISPONÍVEL',
            imagem: null,
            imagem_file: null,
            quantidade: 10,
            estoque_minimo: 5,
            valor_atacado: null,
            valor_compra: null,
            valor_unitario: 100.00,
            valor_custo: null,
            preco_promocional: null,
            ipi: null,
            percentual_custo: null,
            margem_lucro: null,
            ativo: true,
            destaque: false,
            data_compra: null,
        );

        $this->assertFalse($dtoSemPromocao->temPromocao());
        $this->assertEquals('', $dtoSemPromocao->getPrecoPromocionalFormatado());
    }

    public function test_dto_gets_status_correctly()
    {
        // Produto ativo e disponível
        $dtoDisponivel = new ProductDTO(
            id: 1,
            descricao: 'Teste',
            categoria: 'Teste',
            referencia: 'REF-009',
            slug: 'teste',
            tipo: null,
            disponibilidade: 'DISPONÍVEL',
            imagem: null,
            imagem_file: null,
            quantidade: 10,
            estoque_minimo: 5,
            valor_atacado: null,
            valor_compra: null,
            valor_unitario: 100.00,
            valor_custo: null,
            preco_promocional: null,
            ipi: null,
            percentual_custo: null,
            margem_lucro: null,
            ativo: true,
            destaque: false,
            data_compra: null,
        );

        $this->assertEquals('Disponível', $dtoDisponivel->getStatus());
        $this->assertTrue($dtoDisponivel->isDisponivel());

        // Produto inativo
        $dtoInativo = new ProductDTO(
            id: 2,
            descricao: 'Teste 2',
            categoria: 'Teste',
            referencia: 'REF-010',
            slug: 'teste-2',
            tipo: null,
            disponibilidade: 'DISPONÍVEL',
            imagem: null,
            imagem_file: null,
            quantidade: 10,
            estoque_minimo: 5,
            valor_atacado: null,
            valor_compra: null,
            valor_unitario: 100.00,
            valor_custo: null,
            preco_promocional: null,
            ipi: null,
            percentual_custo: null,
            margem_lucro: null,
            ativo: false,
            destaque: false,
            data_compra: null,
        );

        $this->assertEquals('Inativo', $dtoInativo->getStatus());
        $this->assertFalse($dtoInativo->isDisponivel());

        // Produto sem estoque
        $dtoSemEstoque = new ProductDTO(
            id: 3,
            descricao: 'Teste 3',
            categoria: 'Teste',
            referencia: 'REF-011',
            slug: 'teste-3',
            tipo: null,
            disponibilidade: 'DISPONÍVEL',
            imagem: null,
            imagem_file: null,
            quantidade: 0,
            estoque_minimo: 5,
            valor_atacado: null,
            valor_compra: null,
            valor_unitario: 100.00,
            valor_custo: null,
            preco_promocional: null,
            ipi: null,
            percentual_custo: null,
            margem_lucro: null,
            ativo: true,
            destaque: false,
            data_compra: null,
        );

        $this->assertEquals('Esgotado', $dtoSemEstoque->getStatus());
        $this->assertFalse($dtoSemEstoque->isDisponivel());
    }

    public function test_dto_gets_image_url()
    {
        // Com imagem
        $dtoComImagem = new ProductDTO(
            id: 1,
            descricao: 'Teste',
            categoria: 'Teste',
            referencia: 'REF-012',
            slug: 'teste',
            tipo: null,
            disponibilidade: 'DISPONÍVEL',
            imagem: 'produto.jpg',
            imagem_file: null,
            quantidade: 10,
            estoque_minimo: 5,
            valor_atacado: null,
            valor_compra: null,
            valor_unitario: 100.00,
            valor_custo: null,
            preco_promocional: null,
            ipi: null,
            percentual_custo: null,
            margem_lucro: null,
            ativo: true,
            destaque: false,
            data_compra: null,
        );

        $this->assertStringContainsString('storage/produtos/produto.jpg', $dtoComImagem->getImagemUrl());

        // Sem imagem
        $dtoSemImagem = new ProductDTO(
            id: 2,
            descricao: 'Teste 2',
            categoria: 'Teste',
            referencia: 'REF-013',
            slug: 'teste-2',
            tipo: null,
            disponibilidade: 'DISPONÍVEL',
            imagem: null,
            imagem_file: null,
            quantidade: 10,
            estoque_minimo: 5,
            valor_atacado: null,
            valor_compra: null,
            valor_unitario: 100.00,
            valor_custo: null,
            preco_promocional: null,
            ipi: null,
            percentual_custo: null,
            margem_lucro: null,
            ativo: true,
            destaque: false,
            data_compra: null,
        );

        $this->assertStringContainsString('images/produto-placeholder.jpg', $dtoSemImagem->getImagemUrl());
    }
}