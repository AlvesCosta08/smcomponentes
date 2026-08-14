<?php
// app/Services/DashboardService.php

namespace App\Services;

use App\Models\Pedido;
use App\Models\Produto;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class DashboardService
{
    /**
     * Obter todas as estatísticas do dashboard
     */
    public function getStats(): array
    {
        return [
            'usuarios' => $this->getUserStats(),
            'produtos' => $this->getProductStats(),
            'pedidos' => $this->getOrderStats(),
            'faturamento' => $this->getRevenueStats(),
            'vendas_mensais' => $this->getMonthlySales(),
            'clientes_top' => $this->getTopCustomers(),
        ];
    }

    /**
     * Estatísticas de usuários
     */
    protected function getUserStats(): array
    {
        return [
            'total' => User::count(),
            'novos_hoje' => User::whereDate('created_at', today())->count(),
            'clientes' => User::role('Cliente')->count(),
            'admins' => User::role('Admin')->count(),
            'funcionarios' => User::role('Funcionario')->count(),
            'ativos' => User::where('ativo', true)->count(),
            'inativos' => User::where('ativo', false)->count(),
        ];
    }

    /**
     * Estatísticas de produtos
     */
    protected function getProductStats(): array
    {
        return [
            'total' => Produto::count(),
            'ativos' => Produto::where('ativo', true)->count(),
            'inativos' => Produto::where('ativo', false)->count(),
            'estoque_baixo' => Produto::where('quantidade', '>', 0)
                ->where('quantidade', '<=', 5)
                ->where('ativo', true)
                ->get(),
            'estoque_zero' => Produto::where('quantidade', 0)
                ->where('ativo', true)
                ->get(),
        ];
    }

    /**
     * Estatísticas de pedidos
     */
    protected function getOrderStats(): array
    {
        $pedidosStatus = Pedido::select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();

        return [
            'total' => Pedido::count(),
            'pendentes' => $pedidosStatus['pendente'] ?? 0,
            'processando' => $pedidosStatus['processando'] ?? 0,
            'enviados' => $pedidosStatus['enviado'] ?? 0,
            'entregues' => $pedidosStatus['entregue'] ?? 0,
            'cancelados' => $pedidosStatus['cancelado'] ?? 0,
            'hoje' => Pedido::whereDate('created_at', today())->count(),
            'ultimos' => Pedido::with('user')
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get(),
        ];
    }

    /**
     * Estatísticas de faturamento
     */
    protected function getRevenueStats(): array
    {
        return [
            'total' => Pedido::where('status', 'entregue')->sum('total') ?? 0,
            'mes' => Pedido::where('status', 'entregue')
                ->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->sum('total') ?? 0,
            'dia' => Pedido::where('status', 'entregue')
                ->whereDate('created_at', today())
                ->sum('total') ?? 0,
            'media_dia' => Pedido::where('created_at', '>=', now()->subDays(30))
                ->where('status', 'entregue')
                ->count() / 30,
        ];
    }

    /**
     * Vendas mensais (últimos 6 meses)
     */
    protected function getMonthlySales(): array
    {
        $meses = [
            '01' => 'Jan', '02' => 'Fev', '03' => 'Mar',
            '04' => 'Abr', '05' => 'Mai', '06' => 'Jun',
            '07' => 'Jul', '08' => 'Ago', '09' => 'Set',
            '10' => 'Out', '11' => 'Nov', '12' => 'Dez'
        ];

        return Pedido::where('status', 'entregue')
            ->select(
                DB::raw('DATE_FORMAT(created_at, "%Y-%m") as mes'),
                DB::raw('SUM(total) as total')
            )
            ->where('created_at', '>=', now()->subMonths(6))
            ->groupBy('mes')
            ->orderBy('mes', 'asc')
            ->get()
            ->map(function($item) use ($meses) {
                $parts = explode('-', $item->mes);
                return [
                    'mes' => $meses[$parts[1]] ?? $parts[1],
                    'total' => $item->total
                ];
            })
            ->toArray();
    }

    /**
     * Top clientes
     */
    protected function getTopCustomers(): array
    {
        return User::select('users.*')
            ->selectRaw('COUNT(pedidos.id) as total_pedidos')
            ->selectRaw('SUM(pedidos.total) as total_gasto')
            ->join('pedidos', 'users.id', '=', 'pedidos.user_id')
            ->where('pedidos.status', 'entregue')
            ->groupBy('users.id')
            ->orderBy('total_gasto', 'desc')
            ->limit(5)
            ->get()
            ->toArray();
    }
}