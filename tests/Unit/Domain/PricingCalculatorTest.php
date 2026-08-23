<?php

namespace Tests\Unit\Domain;

use App\Domain\Produtos\Services\PricingCalculator;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class PricingCalculatorTest extends TestCase
{
    private PricingCalculator $calculator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->calculator = new PricingCalculator();
    }

    public function test_calcula_preco_corretamente_com_margem_valida(): void
    {
        // Custo: 100, Margem: 80%, IPI: 10%
        $resultado = $this->calculator->calculate(100.00, 80.00, 10.00);

        // Preço = 100 / (1 - 0.80) = 500
        $this->assertEquals(100.00, $resultado['valor_custo']);
        $this->assertEquals(500.00, $resultado['valor_atacado']);
        $this->assertEquals(20.00, $resultado['percentual_custo']);
    }

public function test_lanca_excecao_se_margem_for_menor_que_60()
{
    $this->expectException(\InvalidArgumentException::class);
    // Atualize a mensagem para refletir a correção matemática
    $this->expectExceptionMessage('A margem de lucro deve estar entre 60% e 99.9%.'); 

    $this->calculator->calculate(100.00, 50.00, 0.00);
}

    public function test_lanca_excecao_se_margem_for_maior_que_150(): void
    {
        $this->expectException(InvalidArgumentException::class);
        
        $this->calculator->calculate(100.00, 160.00, 0.00);
    }
}