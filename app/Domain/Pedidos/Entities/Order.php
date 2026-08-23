<?php

namespace App\Domain\Pedidos\Entities;

use App\Domain\Pedidos\Enums\OrderStatusEnum;
use App\Domain\Pedidos\Enums\PaymentStatusEnum;
use App\Domain\Pedidos\Exceptions\InvalidOrderStatusException;

class Order
{
    public function __construct(
        private readonly int $userId,
        private array $items, // Array de ['product_id' => int, 'quantity' => int, 'unit_price' => float]
        private OrderStatusEnum $status = OrderStatusEnum::PENDENTE,
        private PaymentStatusEnum $paymentStatus = PaymentStatusEnum::AGUARDANDO,
        private ?float $total = null,
        private ?int $id = null
    ) {
        $this->calculateTotal();
    }

    public function getId(): ?int { return $this->id; }
    public function getUserId(): int { return $this->userId; }
    public function getItems(): array { return $this->items; }
    public function getStatus(): OrderStatusEnum { return $this->status; }
    public function getPaymentStatus(): PaymentStatusEnum { return $this->paymentStatus; }
    public function getTotal(): float { return $this->total ?? 0.0; }

    /**
     * Invariante: O total é sempre a soma dos itens.
     */
    private function calculateTotal(): void
    {
        $this->total = array_reduce($this->items, function (float $carry, array $item) {
            return $carry + ($item['quantity'] * $item['unit_price']);
        }, 0.0);
    }

    /**
     * Regra de Negócio: Protege a transição de estado.
     */
    public function markAsPaid(): void
    {
        if ($this->status !== OrderStatusEnum::PENDENTE) {
            throw new InvalidOrderStatusException("Apenas pedidos pendentes podem ser marcados como pagos.");
        }
        $this->status = OrderStatusEnum::PAGO;
        $this->paymentStatus = PaymentStatusEnum::APROVADO;
    }

    /**
     * Regra de Negócio: Valida se o cancelamento é permitido.
     */
    public function cancel(): void
    {
        if (!$this->status->canBeCanceled()) {
            throw new InvalidOrderStatusException("Não é possível cancelar um pedido com status: {$this->status->label()}");
        }
        $this->status = OrderStatusEnum::CANCELADO;
        $this->paymentStatus = PaymentStatusEnum::ESTORNADO; // Simplificação para o exemplo
    }

    public function toArray(): array
    {
        return [
            'user_id' => $this->userId,
            'status' => $this->status->value,
            'payment_status' => $this->paymentStatus->value,
            'total' => $this->getTotal(),
        ];
    }
}