<?php
// app/Enums/TipoProdutoEnum.php

namespace App\Enums;

enum TipoProdutoEnum: string
{
    case UNIDADE = 'UNI';
    case PECA = 'PÇ';
    case CAIXA = 'CX';
    case PACOTE = 'PCO';
    case KIT = 'KIT';
    case METRO = 'M';

    public function label(): string
    {
        return match($this) {
            self::UNIDADE => 'Unidade',
            self::PECA => 'Peça',
            self::CAIXA => 'Caixa',
            self::PACOTE => 'Pacote',
            self::KIT => 'Kit',
            self::METRO => 'Metro',
        };
    }

    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn($case) => [$case->value => $case->label()])
            ->toArray();
    }
}