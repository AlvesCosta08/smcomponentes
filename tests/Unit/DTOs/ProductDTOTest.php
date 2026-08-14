<?php
// tests/Unit/DTOs/ProductDTOTest.php

namespace Tests\Unit\DTOs;

use App\DTOs\ProductDTO;
use App\Models\Produto;
use Illuminate\Http\Request;
use Tests\TestCase;

class ProductDTOTest extends TestCase
{
    public function test_can_create_dto_from_request()
    {
        $request = new Request();
        $request->merge([
            'descricao' => 'Produto Teste',
            'categoria' => 'Eletrônicos',
            'referencia' => 'REF-001',
            'valor_unitario' => 99.90,
            'quantidade' => 10,
        ]);

        $dto = ProductDTO::fromRequest($request);

        $this->assertEquals('Produto Teste', $dto->descricao);
        $this->assertEquals('REF-001', $dto->referencia);
        $this->assertEquals(99.90, $dto->valor_unitario);
        $this->assertEquals(10, $dto->quantidade);
    }

    public function test_can_create_dto_from_model()
    {
        $produto = Produto::factory()->create([
            'descricao' => 'Produto Model',
            'referencia' => 'REF-002',
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
}