<?php

namespace App\Application\Pedidos\DTOs;

final readonly class CreateOrderDTO
{
    public function __construct(
        public int $userId,
        public array $items, // [['product_id' => 1, 'quantity' => 2, 'unit_price' => 50.00], ...]
        public ?string $shippingAddress = null,
        public ?string $notes = null
    ) {}

    public static function fromRequest(int $userId, array $data): self
    {
        return new self(
            userId: $userId,
            items: $data['items'],
            shippingAddress: $data['shipping_address'] ?? null,
            notes: $data['notes'] ?? null
        );
    }
}