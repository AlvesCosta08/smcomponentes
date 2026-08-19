<?php
// app/DTOs/Responses/OrderResponseDTO.php

namespace App\DTOs\Responses;

use App\Models\Pedido;
use App\Models\PedidoItem;

class OrderResponseDTO
{
    public function __construct(
        public readonly int $id,
        public readonly string $numero_pedido,
        public readonly float $subtotal,
        public readonly float $desconto,
        public readonly float $total,
        public readonly string $status,
        public readonly string $forma_pagamento,
        public readonly string $status_pagamento,
        public readonly string $endereco_entrega,
        public readonly string $cidade,
        public readonly string $estado,
        public readonly string $cep,
        public readonly ?string $telefone,
        public readonly ?string $observacoes,
        public readonly array $itens,
        public readonly string $created_at,
        public readonly ?string $updated_at,
    ) {}

    /**
     * Criar a partir do Model
     */
    public static function fromModel(Pedido $pedido): self
    {
        return new self(
            id: $pedido->id,
            numero_pedido: $pedido->numero_pedido,
            subtotal: (float) $pedido->subtotal,
            desconto: (float) $pedido->desconto,
            total: (float) $pedido->total,
            status: $pedido->status,
            forma_pagamento: $pedido->forma_pagamento,
            status_pagamento: $pedido->status_pagamento,
            endereco_entrega: $pedido->endereco_entrega,
            cidade: $pedido->cidade,
            estado: $pedido->estado,
            cep: $pedido->cep,
            telefone: $pedido->telefone ?? null,
            observacoes: $pedido->observacoes ?? null,
            itens: $pedido->itens->map(fn($item) => [
                'id' => $item->id,
                'produto_id' => $item->produto_id,
                'nome' => $item->nome_produto,
                'quantidade' => $item->quantidade,
                'preco_unitario' => (float) $item->preco_unitario,
                'subtotal' => (float) $item->subtotal,
                'imagem' => $item->imagem_produto,
            ])->toArray(),
            created_at: $pedido->created_at->format('d/m/Y H:i'),
            updated_at: $pedido->updated_at?->format('d/m/Y H:i'),
        );
    }

    /**
     * Formatar para API
     */
    public function toApiResponse(): array
    {
        return [
            'id' => $this->id,
            'numero_pedido' => $this->numero_pedido,
            'subtotal' => 'R$ ' . number_format($this->subtotal, 2, ',', '.'),
            'desconto' => 'R$ ' . number_format($this->desconto, 2, ',', '.'),
            'total' => 'R$ ' . number_format($this->total, 2, ',', '.'),
            'status' => $this->status,
            'status_pagamento' => $this->status_pagamento,
            'endereco' => $this->endereco_entrega,
            'cidade' => $this->cidade,
            'estado' => $this->estado,
            'itens' => $this->itens,
            'created_at' => $this->created_at,
        ];
    }

    public function getStatusLabel(): string
    {
        return match($this->status) {
            'pendente' => '🟡 Pendente',
            'confirmado' => '🔵 Confirmado',
            'preparando' => '🟠 Preparando',
            'enviado' => '📦 Enviado',
            'entregue' => '✅ Entregue',
            'cancelado' => '❌ Cancelado',
            default => $this->status,
        };
    }

    public function getStatusPagamentoLabel(): string
    {
        return match($this->status_pagamento) {
            'aguardando' => '🟡 Aguardando',
            'pago' => '✅ Pago',
            'recusado' => '❌ Recusado',
            'cancelado' => '❌ Cancelado',
            default => $this->status_pagamento,
        };
    }
}