<?php

namespace App\Domain\Pedidos\ValueObjects;

final readonly class ShippingAddress
{
    public function __construct(
        public string $endereco,
        public string $cidade,
        public string $estado,
        public string $cep
    ) {}

    public function toArray(): array
    {
        return [
            'endereco_entrega' => $this->endereco,
            'cidade' => $this->cidade,
            'estado' => $this->estado,
            'cep' => $this->cep,
        ];
    }
}