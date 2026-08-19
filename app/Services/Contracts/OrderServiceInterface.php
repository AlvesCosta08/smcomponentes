<?php
// app/Services/Contracts/OrderServiceInterface.php

namespace App\Services\Contracts;

use App\DTOs\OrderDTO;
use App\DTOs\Responses\OrderResponseDTO;
use App\Models\Pedido;
use Illuminate\Pagination\LengthAwarePaginator;

interface OrderServiceInterface
{
    /**
     * Criar um novo pedido
     *
     * @param OrderDTO $dto Dados do pedido
     * @param array $carrinho Itens do carrinho [produto_id => ['quantidade' => X, 'preco' => Y]]
     * @return OrderResponseDTO Pedido criado
     * @throws \App\Exceptions\OutOfStockException
     * @throws \Exception
     */
    public function createOrder(OrderDTO $dto, array $carrinho): OrderResponseDTO;

    /**
     * Cancelar um pedido
     *
     * @param Pedido $pedido Pedido a ser cancelado
     * @return bool True se cancelado com sucesso
     * @throws \Exception
     */
    public function cancelOrder(Pedido $pedido): bool;

    /**
     * Obter pedidos do usuário com paginação
     *
     * @param int $userId ID do usuário
     * @param int $perPage Itens por página
     * @return LengthAwarePaginator
     */
    public function getUserOrders(int $userId, int $perPage = 10): LengthAwarePaginator;

    /**
     * Obter detalhes de um pedido específico
     *
     * @param int $orderId ID do pedido
     * @param int $userId ID do usuário (para verificar permissão)
     * @return OrderResponseDTO|null
     */
    public function getOrderDetails(int $orderId, int $userId): ?OrderResponseDTO;

    /**
     * Reembolsar um pedido
     *
     * @param Pedido $pedido Pedido a ser reembolsado
     * @return bool
     * @throws \Exception
     */
    public function refundOrder(Pedido $pedido): bool;

    /**
     * Calcular subtotal do carrinho
     *
     * @param array $carrinho Itens do carrinho
     * @return float
     */
    public function calculateSubtotal(array $carrinho): float;

    /**
     * Calcular desconto aplicável
     *
     * @param array $carrinho Itens do carrinho
     * @param float $subtotal Subtotal calculado
     * @return float
     */
    public function calculateDiscount(array $carrinho, float $subtotal): float;

    /**
     * Validar se o carrinho pode ser convertido em pedido
     *
     * @param array $carrinho Itens do carrinho
     * @throws \App\Exceptions\OutOfStockException
     * @throws \Exception
     */
    public function validateOrder(array $carrinho): void;

    /**
     * Obter status do pedido para exibição
     *
     * @param Pedido $pedido
     * @return array ['label' => string, 'color' => string, 'icon' => string]
     */
    public function getOrderStatusInfo(Pedido $pedido): array;

    /**
     * Verificar se o pedido pode ser cancelado pelo usuário
     *
     * @param Pedido $pedido
     * @return bool
     */
    public function canUserCancelOrder(Pedido $pedido): bool;

    /**
     * Obter estatísticas rápidas do usuário
     *
     * @param int $userId
     * @return array ['total' => int, 'pendentes' => int, 'entregues' => int, 'cancelados' => int]
     */
    public function getUserOrderStats(int $userId): array;
}