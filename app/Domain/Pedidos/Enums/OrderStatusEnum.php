<?php

namespace App\Domain\Pedidos\Enums;

enum OrderStatusEnum: string
{
    case PENDENTE = 'pendente';
    case PAGO = 'pago';
    case PROCESSANDO = 'processando';
    case ENVIADO = 'enviado';
    case ENTREGUE = 'entregue';
    case CANCELADO = 'cancelado';

    public function label(): string
    {
        return match($this) {
            self::PENDENTE => 'Pendente',
            self::PAGO => 'Pago',
            self::PROCESSANDO => 'Processando',
            self::ENVIADO => 'Enviado',
            self::ENTREGUE => 'Entregue',
            self::CANCELADO => 'Cancelado',
        };
    }

    /**
     * Regra de Negócio: Um pedido só pode ser cancelado se estiver pendente ou pago.
     */
    public function canBeCanceled(): bool
    {
        return in_array($this, [self::PENDENTE, self::PAGO], true);
    }
}