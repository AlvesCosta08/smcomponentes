<?php

namespace App\Application\Pedidos\Handlers;

use App\DTOs\Requests\UpdateOrderStatusDTO;
use App\Domain\Pedidos\Exceptions\InvalidOrderStatusException;
use App\Domain\Pedidos\Repositories\OrderRepositoryInterface;
use App\Models\Pedido;

class UpdateOrderStatusHandler
{
    public function __construct(
        private readonly OrderRepositoryInterface $orderRepository,
    ) {}

    public function handle(int $orderId, UpdateOrderStatusDTO $dto): Pedido
    {
        $pedido = $this->orderRepository->findById($orderId);

        if (!$pedido) {
            throw new InvalidOrderStatusException('Pedido não encontrado.');
        }

        // Validar transição de status
        $this->validateStatusTransition($pedido, $dto->status);

        // Atualizar status
        $this->orderRepository->update($pedido, [
            'status' => $dto->status->value,
            'status_pagamento' => $dto->status_pagamento?->value ?? $pedido->status_pagamento,
        ]);

        return $pedido->fresh();
    }

    private function validateStatusTransition(Pedido $pedido, $newStatus): void
    {
        $currentStatus = $pedido->status;

        // Regras de negócio para transições de status
        $allowedTransitions = [
            'pendente' => ['pago', 'cancelado'],
            'pago' => ['processando', 'cancelado'],
            'processando' => ['enviado', 'cancelado'],
            'enviado' => ['entregue'],
            'entregue' => [], // Pedido entregue não pode mudar de status
            'cancelado' => [], // Pedido cancelado não pode mudar de status
        ];

        if (!in_array($newStatus->value, $allowedTransitions[$currentStatus->value] ?? [])) {
            throw new InvalidOrderStatusException(
                "Não é possível alterar o status de '{$currentStatus->label()}' para '{$newStatus->label()}'."
            );
        }
    }
}