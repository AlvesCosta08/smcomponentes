<?php

namespace App\Services;

class MargemService
{
    /**
     * Retorna as margens de lucro disponíveis
     */
    public static function getMargensDisponiveis(): array
    {
        return [
            20 => '20% - Lucro Baixo',
            30 => '30% - Lucro Médio',
            40 => '40% - Lucro Bom',
            50 => '50% - Lucro Ótimo',
            60 => '60% - Lucro Excelente',
            80 => '80% - Lucro Premium',
            100 => '100% - Lucro Máximo',
        ];
    }
    
    /**
     * Retorna as margens para o formulário (formato para Select)
     */
    public static function getMargensForSelect(): array
    {
        $margens = self::getMargensDisponiveis();
        $result = [];
        foreach ($margens as $value => $label) {
            $result[] = [
                'value' => $value,
                'label' => $label
            ];
        }
        return $result;
    }
}