<?php

namespace App\Domain\Pedidos\Enums;

enum StatusPedidoEnum: string
{
    case PENDENTE = 'pendente';
    case PAGO = 'pago';
    case PROCESSANDO = 'processando';
    case ENVIADO = 'enviado';
    case ENTREGUE = 'entregue';
    case CANCELADO = 'cancelado';

    /**
     * Retorna o label do status para exibição
     */
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
    public function podeSerCancelado(): bool
    {
        return in_array($this, [self::PENDENTE, self::PAGO], true);
    }

    /**
     * Alias para podeSerCancelado() - Compatibilidade
     */
    public function canBeCanceled(): bool
    {
        return $this->podeSerCancelado();
    }

    /**
     * Retorna a cor do status para a interface
     */
    public function color(): string
    {
        return match($this) {
            self::PENDENTE => 'warning',
            self::PAGO => 'success',
            self::PROCESSANDO => 'info',
            self::ENVIADO => 'primary',
            self::ENTREGUE => 'success',
            self::CANCELADO => 'danger',
        };
    }

    /**
     * Retorna o ícone do status para a interface
     */
    public function icon(): string
    {
        return match($this) {
            self::PENDENTE => 'bi-clock',
            self::PAGO => 'bi-credit-card',
            self::PROCESSANDO => 'bi-arrow-repeat',
            self::ENVIADO => 'bi-truck',
            self::ENTREGUE => 'bi-check-circle',
            self::CANCELADO => 'bi-x-circle',
        };
    }

    /**
     * Verifica se o pedido está em um status final
     */
    public function isFinal(): bool
    {
        return in_array($this, [
            self::ENTREGUE,
            self::CANCELADO,
        ], true);
    }

    /**
     * Verifica se o pedido está pendente
     */
    public function isPending(): bool
    {
        return $this === self::PENDENTE;
    }

    /**
     * Verifica se o pedido está pago
     */
    public function isPaid(): bool
    {
        return $this === self::PAGO;
    }

    /**
     * Retorna todos os valores do enum
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Cria um enum a partir de uma string com suporte a valores alternativos
     */
    public static function fromString(string $value): ?self
    {
        $map = [
            'cancelled' => self::CANCELADO,
            'pending' => self::PENDENTE,
            'paid' => self::PAGO,
            'processing' => self::PROCESSANDO,
            'shipped' => self::ENVIADO,
            'delivered' => self::ENTREGUE,
            'cancelado' => self::CANCELADO,
            'pendente' => self::PENDENTE,
            'pago' => self::PAGO,
            'processando' => self::PROCESSANDO,
            'enviado' => self::ENVIADO,
            'entregue' => self::ENTREGUE,
        ];

        if (isset($map[$value])) {
            return $map[$value];
        }

        try {
            return self::from($value);
        } catch (\ValueError $e) {
            return null;
        }
    }
}