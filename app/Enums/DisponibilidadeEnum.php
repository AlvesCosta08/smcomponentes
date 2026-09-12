<?php
// app/Enums/DisponibilidadeEnum.php

namespace App\Enums;

enum DisponibilidadeEnum: string
{
    case DISPONIVEL = 'disponivel';
    case INDISPONIVEL = 'indisponivel';
    case SOB_ENCOMENDA = 'sob_encomenda';

    public function label(): string
    {
        return match($this) {
            self::DISPONIVEL => 'Disponível',
            self::INDISPONIVEL => 'Indisponível',
            self::SOB_ENCOMENDA => 'Sob Encomenda',
        };
    }

    public function badgeColor(): string
    {
        return match($this) {
            self::DISPONIVEL => 'success',
            self::INDISPONIVEL => 'danger',
            self::SOB_ENCOMENDA => 'warning',
        };
    }

    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn($case) => [$case->value => $case->label()])
            ->toArray();
    }
}