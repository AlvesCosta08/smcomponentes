<?php
// app/Services/OrderService.php

namespace App\Services;

use App\DTOs\OrderDTO;
use App\DTOs\Responses\OrderResponseDTO;
use App\Exceptions\OutOfStockException;
use App\Models\Pedido;
use App\Models\PedidoItem;
use App\Models\Produto;
use App\Repositories\PedidoRepository;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OrderService
{
    public function __construct(
        protected PedidoRepository $repository,
        protected StockService $stockService
    ) {}

    /**
     * Criar um novo pedido
     */
    public function createOrder(OrderDTO $dto, array $carrinho): OrderResponseDTO
    {
        try {
            DB::beginTransaction();

            // Verificar estoque de todos os itens
            $this->validateStock($carrinho);

            // Calcular valores
            $subtotal = $this->calculateSubtotal($carrinho);
            $desconto = $this->calculateDiscount($carrinho);
            $total = $subtotal - $desconto;

            // Criar pedido
            $pedido = $this->repository->create([
                'user_id' => Auth::id(),
                'numero_pedido' => Pedido::gerarNumeroPedido(),
                'subtotal' => $subtotal,
                'desconto' => $desconto,
                'total' => $total,
                'status' => 'pendente',
                'forma_pagamento' => $dto->forma_pagamento,
                'status_pagamento' => 'aguardando',
                'observacoes' => $dto->observacoes,
                'endereco_entrega' => $dto->endereco,
                'cidade' => $dto->cidade,
                'estado' => $dto->estado,
                'cep' => $dto->cep,
                'telefone' => $dto->telefone ?? null,
            ]);

            // Criar itens do pedido e reservar estoque
            foreach ($carrinho as $id => $item) {
                $produto = Produto::find($id);
                
                if (!$produto) {
                    throw new \Exception("Produto não encontrado: {$id}");
                }

                // Verificar estoque novamente (por segurança)
                if (!$produto->temEstoque($item['quantidade'])) {
                    throw new OutOfStockException(
                        "Estoque insuficiente para: {$produto->descricao}. " .
                        "Disponível: {$produto->quantidade}, Solicitado: {$item['quantidade']}"
                    );
                }

                // Criar item do pedido
                PedidoItem::create([
                    'pedido_id' => $pedido->id,
                    'produto_id' => $id,
                    'quantidade' => $item['quantidade'],
                    'preco_unitario' => $item['preco'],
                    'preco_promocional' => $item['preco_promocional'] ?? null,
                    'subtotal' => $item['preco'] * $item['quantidade'],
                    'nome_produto' => $produto->descricao,
                    'imagem_produto' => $produto->imagem
                ]);

                // Reservar estoque (reduzir)
                $this->stockService->reserveStock($produto, $item['quantidade'], false);
            }

            DB::commit();

            Log::info('Pedido criado com sucesso', [
                'pedido_id' => $pedido->id,
                'user_id' => Auth::id(),
                'total' => $total,
                'itens' => count($carrinho)
            ]);

            return OrderResponseDTO::fromModel($pedido);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao criar pedido: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'carrinho' => $carrinho
            ]);
            throw $e;
        }
    }

    /**
     * Cancelar pedido
     */
    public function cancelOrder(Pedido $pedido): bool
    {
        try {
            DB::beginTransaction();

            // Verificar se pode cancelar
            if (!$pedido->podeCancelar()) {
                throw new \Exception('Este pedido não pode ser cancelado');
            }

            // Restaurar estoque
            foreach ($pedido->itens as $item) {
                $produto = Produto::find($item->produto_id);
                if ($produto) {
                    $this->stockService->releaseStock($produto, $item->quantidade);
                }
            }

            // Atualizar status
            $pedido->update(['status' => 'cancelado']);

            DB::commit();

            Log::info('Pedido cancelado com sucesso', [
                'pedido_id' => $pedido->id,
                'user_id' => Auth::id()
            ]);

            return true;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao cancelar pedido: ' . $e->getMessage(), [
                'pedido_id' => $pedido->id
            ]);
            throw $e;
        }
    }

    /**
     * Obter pedidos do usuário
     */
    public function getUserOrders(int $userId, int $perPage = 10)
    {
        return Pedido::where('user_id', $userId)
            ->with('itens')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Obter detalhes do pedido
     */
    public function getOrderDetails(int $id, int $userId): ?OrderResponseDTO
    {
        $pedido = Pedido::with('itens')
            ->where('id', $id)
            ->where('user_id', $userId)
            ->first();

        if (!$pedido) {
            return null;
        }

        return OrderResponseDTO::fromModel($pedido);
    }

    /**
     * Validar estoque de todos os itens
     */
    protected function validateStock(array $carrinho): void
    {
        foreach ($carrinho as $id => $item) {
            $produto = Produto::find($id);
            
            if (!$produto) {
                throw new \Exception("Produto não encontrado: {$id}");
            }

            if (!$produto->temEstoque($item['quantidade'])) {
                throw new OutOfStockException(
                    "Produto '{$produto->descricao}' não tem estoque suficiente. " .
                    "Disponível: {$produto->quantidade}, Solicitado: {$item['quantidade']}"
                );
            }
        }
    }

    /**
     * Calcular subtotal
     */
    protected function calculateSubtotal(array $carrinho): float
    {
        $subtotal = 0;
        foreach ($carrinho as $item) {
            $preco = $item['preco'] ?? $item['preco_unitario'] ?? 0;
            $subtotal += $preco * $item['quantidade'];
        }
        return $subtotal;
    }

    /**
     * Calcular desconto
     */
    protected function calculateDiscount(array $carrinho): float
    {
        // Lógica para descontos (frete grátis, cupom, etc)
        // Por enquanto retorna 0
        return 0;
    }
}