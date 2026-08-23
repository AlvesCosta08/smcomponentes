<?php

namespace App\Domain\Produtos\ValueObjects;

use InvalidArgumentException;

final class Money
{
    public function __construct(
        private readonly float $amount,
        private readonly string $currency = 'BRL'
    ) {
        if ($amount < 0) {
            throw new InvalidArgumentException('O valor não pode ser negativo.');
        }
    }

    public function amount(): float
    {
        return $this->amount;
    }

    public function currency(): string
    {
        return $this->currency;
    }

    public function format(): string
    {
        return 'R$ ' . number_format($this->amount, 2, ',', '.');
    }

    public function equals(Money $other): bool
    {
        return $this->amount === $other->amount && $this->currency === $other->currency;
    }
}