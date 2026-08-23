<?php

namespace App\Domain\Produtos\Services;

use InvalidArgumentException;

final class PricingCalculator
{
    /**
     * Calcula os preços com base na regra de negócio (Margem Bruta).
     *
     * @param float $valorCompra Preço de custo do produto
     * @param float $margemLucro Porcentagem de margem de lucro (60 a 99.9)
     * @param float $ipi Porcentagem de IPI (0 a 100)
     * @return array{
     *     valor_custo: float,
     *     valor_atacado: float,
     *     percentual_custo: float,
     *     valor_ipi: float,
     *     preco_com_ipi: float
     * }
     */
    public function calculate(float $valorCompra, float $margemLucro, float $ipi = 0.0): array
    {
        // 🔒 REGRA DE NEGÓCIO: Margem Bruta deve ser < 100% para evitar divisão por zero ou preço negativo.
        // (Nota: Se a sua regra de negócio exige "até 150%", você está usando a lógica de MARKUP, não de Margem. Veja a observação abaixo).
        if ($margemLucro < 60.0 || $margemLucro >= 100.0) {
            throw new InvalidArgumentException('A margem de lucro deve estar entre 60% e 99.9%.');
        }

        if ($valorCompra < 0) {
            throw new InvalidArgumentException('O valor de compra não pode ser negativo.');
        }

        if ($ipi < 0 || $ipi > 100) {
            throw new InvalidArgumentException('O IPI deve estar entre 0% e 100%.');
        }

        $custo = round($valorCompra, 2);
        
        // Fórmula de Margem Bruta: Preço = Custo / (1 - (Margem / 100))
        $precoAtacado = round($custo / (1 - ($margemLucro / 100)), 2);

        $percentualCusto = 0.0;
        if ($precoAtacado > 0) {
            $percentualCusto = round(($custo / $precoAtacado) * 100, 2);
        }

        // Cálculo do IPI sobre o preço de atacado
        $valorIpi = round($precoAtacado * ($ipi / 100), 2);
        $precoComIpi = round($precoAtacado + $valorIpi, 2);

        return [
            'valor_custo' => $custo,
            'valor_atacado' => $precoAtacado,
            'percentual_custo' => $percentualCusto,
            'valor_ipi' => $valorIpi,
            'preco_com_ipi' => $precoComIpi,
        ];
    }
}