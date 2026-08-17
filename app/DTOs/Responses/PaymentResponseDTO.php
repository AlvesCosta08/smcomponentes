<?php
// app/DTOs/Responses/PaymentResponseDTO.php

namespace App\DTOs\Responses;

use App\Models\Pedido;

class PaymentResponseDTO
{
    public function __construct(
        public bool $success,
        public string $payment_id,
        public string $status,
        public Pedido $pedido,
        public ?string $message = null,
        public ?array $extra = null
    ) {}

    public function isApproved(): bool
    {
        return $this->status === 'approved' || $this->status === 'pago';
    }

    public function isPending(): bool
    {
        return $this->status === 'pending' || $this->status === 'pendente';
    }

    public function isRejected(): bool
    {
        return $this->status === 'rejected' || $this->status === 'recusado';
    }

    public function isCancelled(): bool
    {
        return $this->status === 'cancelled' || $this->status === 'cancelado';
    }

    public function toArray(): array
    {
        return [
            'success' => $this->success,
            'payment_id' => $this->payment_id,
            'status' => $this->status,
            'pedido_id' => $this->pedido->id,
            'pedido_numero' => $this->pedido->numero_pedido,
            'pedido_total' => $this->pedido->total,
            'message' => $this->message,
            'extra' => $this->extra,
        ];
    }

    public function toJson(): string
    {
        return json_encode($this->toArray());
    }
}
