<?php

namespace App\Domain\Pedidos\ValueObjects;

use InvalidArgumentException;

final readonly class OrderTotal
{
    public function __construct(
        public float $subtotal,
        public float $desconto = 0.0,
        public float $total = 0.0
    ) {
        // Invariante: O total deve ser exatamente o subtotal menos o desconto
        $calculatedTotal = round($this->subtotal - $this->desconto, 2);
        
        if (abs($this->total - $calculatedTotal) > 0.01) {
            throw new InvalidArgumentException("O total ({$this->total}) deve ser igual ao subtotal ({$this->subtotal}) menos o desconto ({$this->desconto}).");
        }

        if ($this->total < 0) {
            throw new InvalidArgumentException("O total do pedido não pode ser negativo.");
        }
    }
}