<?php
// app/Services/Contracts/DashboardServiceInterface.php

namespace App\Services\Contracts;

interface DashboardServiceInterface
{
    /**
     * Obter dados do dashboard administrativo
     *
     * @return array [
     *   'stats' => array,
     *   'graficos' => array,
     *   'pedidos_recentes' => Collection,
     *   'estoque_critico' => Collection,
     *   'vendas_mensais' => array,
     *   'top_clientes' => Collection
     * ]
     */
    public function getAdminDashboardData(): array;

    /**
     * Obter estatísticas principais
     *
     * @return array [
     *   'total_pedidos' => int,
     *   'total_clientes' => int,
     *   'total_produtos' => int,
     *   'faturamento_hoje' => float,
     *   'faturamento_mes' => float,
     *   'pedidos_pendentes' => int,
     *   'produtos_estoque_baixo' => int
     * ]
     */
    public function getMainStats(): array;

    /**
     * Obter dados do gráfico de vendas
     *
     * @param string $period last_7_days|last_30_days|last_90_days
     * @return array ['labels' => array, 'datasets' => array]
     */
    public function getSalesChartData(string $period = 'last_30_days'): array;

    /**
     * Obter dados do gráfico de status dos pedidos
     *
     * @return array ['labels' => array, 'data' => array, 'colors' => array]
     */
    public function getOrderStatusChartData(): array;

    /**
     * Obter produtos com estoque crítico
     *
     * @param int $threshold
     * @param int $limit
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getCriticalStockProducts(int $threshold = 5, int $limit = 10): \Illuminate\Database\Eloquent\Collection;

    /**
     * Obter últimos pedidos
     *
     * @param int $limit
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getRecentOrders(int $limit = 10): \Illuminate\Database\Eloquent\Collection;

    /**
     * Obter vendas mensais (últimos 12 meses)
     *
     * @return array ['meses' => array, 'valores' => array]
     */
    public function getMonthlySales(): array;

    /**
     * Obter top clientes
     *
     * @param int $limit
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getTopCustomers(int $limit = 10): \Illuminate\Database\Eloquent\Collection;

    /**
     * Obter dados do dashboard do usuário comum
     *
     * @param int $userId
     * @return array [
     *   'ultimos_pedidos' => Collection,
     *   'estatisticas' => array,
     *   'wishlist_count' => int
     * ]
     */
    public function getUserDashboardData(int $userId): array;

    /**
     * Calcular taxa de conversão
     *
     * @param string $period
     * @return float
     */
    public function getConversionRate(string $period = 'last_30_days'): float;

    /**
     * Obter resumo rápido (widgets do dashboard)
     *
     * @return array
     */
    public function getQuickSummary(): array;
}