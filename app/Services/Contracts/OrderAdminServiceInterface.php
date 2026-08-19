<?php
// app/Services/Contracts/OrderAdminServiceInterface.php

namespace App\Services\Contracts;

use App\Models\Pedido;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface OrderAdminServiceInterface
{
    /**
     * Listar pedidos com filtros (admin)
     *
     * @param array $filters ['status' => string, 'data_inicio' => string, 'data_fim' => string, 'search' => string]
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function listOrders(array $filters = [], int $perPage = 15): LengthAwarePaginator;

    /**
     * Obter detalhes do pedido (admin)
     *
     * @param int $id
     * @return Pedido|null
     */
    public function getOrderDetails(int $id): ?Pedido;

    /**
     * Atualizar status do pedido (admin)
     *
     * @param Pedido $pedido
     * @param string $novoStatus
     * @return bool
     */
    public function updateStatus(Pedido $pedido, string $novoStatus): bool;

    /**
     * Excluir pedido (apenas cancelados)
     *
     * @param Pedido $pedido
     * @return bool
     */
    public function deleteOrder(Pedido $pedido): bool;

    /**
     * Obter estatísticas para o dashboard
     *
     * @return array ['total' => int, 'faturado' => float, 'pendentes' => int, 'hoje' => int]
     */
    public function getStats(): array;

    /**
     * Exportar pedidos para CSV
     *
     * @param array $filters
     * @return Collection
     */
    public function export(array $filters = []): Collection;

    /**
     * Relatório de vendas
     *
     * @param string $dataInicio
     * @param string $dataFim
     * @return array
     */
    public function getSalesReport(string $dataInicio, string $dataFim): array;

    /**
     * Obter pedidos por status
     *
     * @param string $status
     * @param int $limit
     * @return Collection
     */
    public function getOrdersByStatus(string $status, int $limit = 50): Collection;

    /**
     * Enviar notificação de status para cliente
     *
     * @param Pedido $pedido
     * @param string $status
     * @return bool
     */
    public function sendStatusNotification(Pedido $pedido, string $status): bool;

    /**
     * Verificar se status pode ser alterado
     *
     * @param Pedido $pedido
     * @param string $novoStatus
     * @return bool
     */
    public function canChangeStatus(Pedido $pedido, string $novoStatus): bool;

    /**
     * Obter histórico de status do pedido
     *
     * @param int $pedidoId
     * @return Collection
     */
    public function getStatusHistory(int $pedidoId): Collection;

    /**
     * Marcar pedido como faturado
     *
     * @param Pedido $pedido
     * @return bool
     */
    public function markAsInvoiced(Pedido $pedido): bool;
}