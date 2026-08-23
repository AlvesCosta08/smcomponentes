<?php

namespace App\Application\Pedidos\Handlers;

use App\DTOs\Requests\CreateOrderDTO;
use App\Domain\Pedidos\Repositories\OrderRepositoryInterface;
use App\Domain\Pedidos\Repositories\OrderItemRepositoryInterface;
use App\Models\Pedido;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CreateOrderHandler
{
    public function __construct(
        private readonly OrderRepositoryInterface $orderRepository,
        private readonly OrderItemRepositoryInterface $itemRepository,
    ) {}

    public function handle(CreateOrderDTO $dto): Pedido
    {
        return DB::transaction(function () use ($dto) {
            // 1. Gerar número único do pedido
            $numeroPedido = $this->generateOrderNumber();

            // 2. Calcular totais
            $subtotal = 0;
            foreach ($dto->items as $item) {
                $subtotal += $item['quantidade'] * $item['preco_unitario'];
            }

            // 3. Criar o pedido
            $pedido = $this->orderRepository->create([
                'user_id' => $dto->userId,
                'numero_pedido' => $numeroPedido,
                'subtotal' => $subtotal,
                'desconto' => 0,
                'total' => $subtotal,
                'status' => 'pendente',
                'status_pagamento' => 'aguardando',
                'observacoes' => $dto->observacoes,
                'endereco_entrega' => $dto->endereco_entrega,
                'cidade' => $dto->cidade,
                'estado' => $dto->estado,
                'cep' => $dto->cep,
            ]);

            // 4. Criar os itens do pedido
            $itemsData = array_map(function ($item) use ($pedido) {
                return [
                    'pedido_id' => $pedido->id,
                    'produto_id' => $item['produto_id'],
                    'quantidade' => $item['quantidade'],
                    'preco_unitario' => $item['preco_unitario'],
                    'preco_promocional' => $item['preco_promocional'] ?? null,
                    'subtotal' => $item['quantidade'] * $item['preco_unitario'],
                    'nome_produto' => $item['nome_produto'] ?? null,
                    'imagem_produto' => $item['imagem_produto'] ?? null,
                ];
            }, $dto->items);

            $this->itemRepository->createMany($itemsData);

            // 5. Retornar pedido com itens carregados
            return $pedido->load('itens.produto');
        });
    }

    private function generateOrderNumber(): string
    {
        return 'PED-' . date('Ymd') . '-' . strtoupper(Str::random(6));
    }
}