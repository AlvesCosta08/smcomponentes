<?php

namespace App\DTOs\Requests;

use Illuminate\Http\Request;

final readonly class CreateOrderDTO
{
    public function __construct(
        public int $userId,
        public array $items, // Array de ['produto_id', 'quantidade', 'preco_unitario', 'preco_promocional', 'nome_produto', 'imagem_produto']
        public ?string $observacoes = null,
        public ?string $endereco_entrega = null,
        public ?string $cidade = null,
        public ?string $estado = null,
        public ?string $cep = null,
    ) {}

    public static function fromRequest(Request $request, int $userId): self
    {
        return new self(
            userId: $userId,
            items: $request->input('items', []),
            observacoes: $request->input('observacoes'),
            endereco_entrega: $request->input('endereco_entrega'),
            cidade: $request->input('cidade'),
            estado: $request->input('estado'),
            cep: preg_replace('/[^0-9]/', '', $request->input('cep')),
        );
    }
}