<?php

namespace App\Domain\Pedidos\Enums;

enum StatusPagamentoEnum: string
{
    case AGUARDANDO = 'aguardando';
    case APROVADO = 'aprovado';
    case RECUSADO = 'recusado';
    case CANCELADO = 'cancelado';
    case ESTORNADO = 'estornado';

    /**
     * Retorna o label do status para exibição
     */
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

    /**
     * Retorna a cor do status para a interface
     */
    public function color(): string
    {
        return match($this) {
            self::AGUARDANDO => 'warning',
            self::APROVADO => 'success',
            self::RECUSADO => 'danger',
            self::CANCELADO => 'secondary',
            self::ESTORNADO => 'info',
        };
    }

    /**
     * Retorna o ícone do status para a interface
     */
    public function icon(): string
    {
        return match($this) {
            self::AGUARDANDO => 'bi-clock',
            self::APROVADO => 'bi-check-circle-fill',
            self::RECUSADO => 'bi-exclamation-circle',
            self::CANCELADO => 'bi-x-circle',
            self::ESTORNADO => 'bi-arrow-counterclockwise',
        };
    }

    /**
     * Verifica se o pagamento pode ser cancelado
     */
    public function canBeCanceled(): bool
    {
        return in_array($this, [
            self::AGUARDANDO,
            self::CANCELADO,
        ], true);
    }

    /**
     * Verifica se o pagamento está em um status final
     */
    public function isFinal(): bool
    {
        return in_array($this, [
            self::APROVADO,
            self::RECUSADO,
            self::ESTORNADO,
        ], true);
    }

    /**
     * Verifica se o pagamento foi aprovado
     */
    public function isApproved(): bool
    {
        return $this === self::APROVADO;
    }

    /**
     * Verifica se o pagamento está pendente
     */
    public function isPending(): bool
    {
        return $this === self::AGUARDANDO;
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
     * ✅ CORRIGIDO: Remover tryFrom() pois já existe nativamente
     * ✅ Usar fromString() para mapeamento de valores em inglês
     */
    public static function fromString(string $value): ?self
    {
        // Mapeamento de valores alternativos (inglês -> português)
        $map = [
            'approved' => self::APROVADO,
            'pending' => self::AGUARDANDO,
            'failed' => self::RECUSADO,
            'rejected' => self::RECUSADO,
            'cancelled' => self::CANCELADO,
            'refunded' => self::ESTORNADO,
            'paid' => self::APROVADO,
            'waiting' => self::AGUARDANDO,
            'denied' => self::RECUSADO,
        ];

        if (isset($map[$value])) {
            return $map[$value];
        }

        // Tenta usar o tryFrom nativo do PHP
        return self::tryFrom($value);
    }

    /**
     * ✅ ADICIONADO: Método para criar a partir de string com fallback
     */
    public static function fromStringOrFail(string $value): self
    {
        return self::fromString($value) ?? self::AGUARDANDO;
    }
}