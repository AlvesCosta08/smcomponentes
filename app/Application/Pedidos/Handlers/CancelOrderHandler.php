<?php

namespace App\Application\Pedidos\Handlers;

use App\Domain\Pedidos\Exceptions\InvalidOrderStatusException;
use App\Domain\Pedidos\Repositories\OrderRepositoryInterface;
use App\Models\Pedido;

class CancelOrderHandler
{
    public function __construct(
        private readonly OrderRepositoryInterface $orderRepository,
    ) {}

    public function handle(int $orderId): Pedido
    {
        $pedido = $this->orderRepository->findById($orderId);

        if (!$pedido) {
            throw new InvalidOrderStatusException('Pedido não encontrado.');
        }

        // Validar se pode ser cancelado
        if (!$pedido->status->canBeCanceled()) {
            throw new InvalidOrderStatusException(
                "Não é possível cancelar um pedido com status '{$pedido->status->label()}'."
            );
        }

        // Cancelar pedido
        $this->orderRepository->update($pedido, [
            'status' => 'cancelado',
            'status_pagamento' => 'estornado',
        ]);

        return $pedido->fresh();
    }
}