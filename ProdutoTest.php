<?php
use PHPUnit\Framework\TestCase;

// Função a ser testada
function converterValorBrasileiro($valor) {
    if (empty($valor) || in_array($valor, ['0', '0,00', '0.00', ''], true)) {
        return 0.0;
    }
    $valor_limpo = preg_replace('/[^\d,\.]/', '', $valor);
    if (empty($valor_limpo)) return 0.0;

    if (strpos($valor_limpo, ',') !== false && strpos($valor_limpo, '.') === false) {
        $valor_limpo = str_replace(',', '.', $valor_limpo);
    } elseif (strpos($valor_limpo, '.') !== false && strpos($valor_limpo, ',') !== false) {
        $valor_limpo = str_replace('.', '', $valor_limpo);
        $valor_limpo = str_replace(',', '.', $valor_limpo);
    }
    return floatval($valor_limpo);
}

class ProdutoTest extends TestCase
{
    public function testConverteValorBrasileiro()
    {
        $this->assertEquals(1200.50, converterValorBrasileiro("1.200,50"));
        $this->assertEquals(1200.50, converterValorBrasileiro("1200,50"));
        $this->assertEquals(1200.50, converterValorBrasileiro("1200.50"));
        $this->assertEquals(1200.50, converterValorBrasileiro("1.200.50"));
        $this->assertEquals(0.0, converterValorBrasileiro(""));
        $this->assertEquals(0.0, converterValorBrasileiro("0"));
        $this->assertEquals(0.0, converterValorBrasileiro("0,00"));
        $this->assertEquals(0.0, converterValorBrasileiro("0.00"));
        $this->assertEquals(0.67, converterValorBrasileiro("0,67"));
        $this->assertEquals(100.0, converterValorBrasileiro("100"));
        $this->assertEquals(100.0, converterValorBrasileiro("100,00"));
        $this->assertEquals(100.0, converterValorBrasileiro("100.00"));
        $this->assertEquals(1234.56, converterValorBrasileiro("1.234,56"));
        $this->assertEquals(1234.56, converterValorBrasileiro("1234,56"));
        $this->assertEquals(1234.56, converterValorBrasileiro("1234.56"));
    }
}