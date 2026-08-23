<?php

namespace App\Domain\Produtos\ValueObjects;

use App\Enums\DisponibilidadeEnum;
use InvalidArgumentException;

final class Stock
{
    public function __construct(
        private readonly int $quantity,
        private readonly int $minimum = 5
    ) {
        if ($quantity < 0) {
            throw new InvalidArgumentException('A quantidade em estoque não pode ser negativa.');
        }
        if ($minimum < 0) {
            throw new InvalidArgumentException('O estoque mínimo não pode ser negativo.');
        }
    }

    public function quantity(): int
    {
        return $this->quantity;
    }

    public function minimum(): int
    {
        return $this->minimum;
    }

    public function isAvailable(): bool
    {
        return $this->quantity > 0;
    }

    public function isLow(): bool
    {
        return $this->quantity > 0 && $this->quantity <= $this->minimum;
    }

    public function getDisponibilidade(): string
    {
        if (!$this->isAvailable()) {
            return DisponibilidadeEnum::INDISPONIVEL->value;
        }
        if ($this->isLow()) {
            return DisponibilidadeEnum::ESTOQUE_BAIXO->value;
        }
        return DisponibilidadeEnum::DISPONIVEL->value;
    }
}