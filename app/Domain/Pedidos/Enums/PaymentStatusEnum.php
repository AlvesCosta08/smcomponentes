<?php

namespace App\Domain\Pedidos\Enums;

enum PaymentStatusEnum: string
{
    case AGUARDANDO = 'aguardando';
    case APROVADO = 'aprovado';
    case RECUSADO = 'recusado';
    case CANCELADO = 'cancelado';
    case ESTORNADO = 'estornado';

    public function label(): string
    {
        return match($this) {
            self::AGUARDANDO => 'Aguardando',
            self::APROVADO => 'Aprovado',
            self::RECUSADO => 'Recusado',
            self::CANCELADO => 'Cancelado',
            self::ESTORNADO => 'Estornado',
        };
    }
}