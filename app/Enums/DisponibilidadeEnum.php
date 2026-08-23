<?php
// app/Enums/DisponibilidadeEnum.php

namespace App\Enums;

enum DisponibilidadeEnum: string
{
    case DISPONIVEL = 'DISPONIVEL';
    case INDISPONIVEL = 'INDISPONIVEL';
    case ESTOQUE_BAIXO = 'ESTOQUE_BAIXO';  // ✅ Corrigido para ESTOQUE_BAIXO

    public function label(): string
    {
        return match($this) {
            self::DISPONIVEL => 'Disponível',
            self::INDISPONIVEL => 'Indisponível',
            self::ESTOQUE_BAIXO => 'Estoque Baixo',
        };
    }

    public function badgeColor(): string
    {
        return match($this) {
            self::DISPONIVEL => 'success',
            self::INDISPONIVEL => 'danger',
            self::ESTOQUE_BAIXO => 'warning',
        };
    }

    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn($case) => [$case->value => $case->label()])
            ->toArray();
    }
}