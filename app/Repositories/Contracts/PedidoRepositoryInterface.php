<?php
// app/Repositories/Contracts/PedidoRepositoryInterface.php

namespace App\Repositories\Contracts;

use App\Models\Pedido;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface PedidoRepositoryInterface extends RepositoryInterface
{
    /**
     * Buscar pedidos por usuário
     *
     * @param int $userId
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function findByUser(int $userId, int $perPage = 10): LengthAwarePaginator;

    /**
     * Buscar pedidos por status
     *
     * @param string $status
     * @param int $limit
     * @return Collection
     */
    public function findByStatus(string $status, int $limit = null): Collection;

    /**
     * Buscar pedidos por período
     *
     * @param string $startDate
     * @param string $endDate
     * @return Collection
     */
    public function findByDateRange(string $startDate, string $endDate): Collection;

    /**
     * Buscar pedido por número
     *
     * @param string $numero
     * @return Pedido|null
     */
    public function findByNumero(string $numero): ?Pedido;

    /**
     * Obter pedidos pendentes
     *
     * @param int $limit
     * @return Collection
     */
    public function getPendentes(int $limit = null): Collection;

    /**
     * Obter pedidos do dia
     *
     * @return Collection
     */
    public function getTodayOrders(): Collection;

    /**
     * Calcular faturamento total
     *
     * @param string|null $status
     * @param string|null $startDate
     * @param string|null $endDate
     * @return float
     */
    public function getTotalRevenue(?string $status = null, ?string $startDate = null, ?string $endDate = null): float;

    /**
     * Calcular ticket médio
     *
     * @param string|null $startDate
     * @param string|null $endDate
     * @return float
     */
    public function getAverageTicket(?string $startDate = null, ?string $endDate = null): float;

    /**
     * Obter quantidade de pedidos por status
     *
     * @return array
     */
    public function getCountByStatus(): array;

    /**
     * Obter vendas por dia (últimos N dias)
     *
     * @param int $days
     * @return array
     */
    public function getSalesByDay(int $days = 30): array;

    /**
     * Obter top clientes
     *
     * @param int $limit
     * @return Collection
     */
    public function getTopCustomers(int $limit = 10): Collection;

    /**
     * Gerar número de pedido único
     *
     * @return string
     */
    public function generateNumero(): string;

    /**
     * Atualizar status do pedido
     *
     * @param int $id
     * @param string $status
     * @param array $extra
     * @return Pedido
     */
    public function updateStatus(int $id, string $status, array $extra = []): Pedido;

    /**
     * Buscar pedidos com filtros avançados
     *
     * @param array $filters
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getFilteredOrders(array $filters = [], int $perPage = 15): LengthAwarePaginator;

    /**
     * Obter relatório de vendas
     *
     * @param string $startDate
     * @param string $endDate
     * @return array
     */
    public function getSalesReport(string $startDate, string $endDate): array;

    /**
     * Verificar se pedido pode ser cancelado
     *
     * @param int $id
     * @return bool
     */
    public function canBeCancelled(int $id): bool;

    /**
     * Obter pedidos com itens
     *
     * @param int $id
     * @return Pedido|null
     */
    public function findWithItems(int $id): ?Pedido;
}