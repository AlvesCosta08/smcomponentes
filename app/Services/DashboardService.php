<?php

namespace App\Services;

use App\Domain\Pedidos\Enums\StatusPedidoEnum;
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
            // CORREÇÃO: Usar count() em vez de get() para evitar sobrecarga de memória
            'estoque_baixo' => Produto::where('ativo', true)
                ->whereBetween('quantidade', [1, 5])
                ->count(),
            'estoque_zero' => Produto::where('ativo', true)
                ->where('quantidade', 0)
                ->count(),
        ];
    }

    /**
     * Estatísticas de pedidos
     */
    protected function getOrderStats(): array
    {
        // CORREÇÃO: Usar o Enum para garantir consistência
        $statusEntregue = StatusPedidoEnum::ENTREGUE->value;

        $pedidosStatus = Pedido::select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();

        return [
            'total' => Pedido::count(),
            'pendentes' => $pedidosStatus[StatusPedidoEnum::PENDENTE->value] ?? 0,
            'processando' => $pedidosStatus[StatusPedidoEnum::PROCESSANDO->value] ?? 0,
            'enviados' => $pedidosStatus[StatusPedidoEnum::ENVIADO->value] ?? 0,
            'entregues' => $pedidosStatus[$statusEntregue] ?? 0,
            'cancelados' => $pedidosStatus[StatusPedidoEnum::CANCELADO->value] ?? 0,
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
        $statusEntregue = StatusPedidoEnum::ENTREGUE->value;

        return [
            // CORREÇÃO: Cast para float para garantir que nunca retorne null ou string inesperada
            'total' => (float) Pedido::where('status', $statusEntregue)->sum('total'),
            'mes' => (float) Pedido::where('status', $statusEntregue)
                ->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->sum('total'),
            'dia' => (float) Pedido::where('status', $statusEntregue)
                ->whereDate('created_at', today())
                ->sum('total'),
            'media_dia' => (float) (Pedido::where('created_at', '>=', now()->subDays(30))
                ->where('status', $statusEntregue)
                ->count() / 30),
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

        $statusEntregue = StatusPedidoEnum::ENTREGUE->value;

        return Pedido::where('status', $statusEntregue)
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
                    'total' => (float) $item->total
                ];
            })
            ->toArray();
    }

    /**
     * Top clientes
     */
    protected function getTopCustomers(): array
    {
        $statusEntregue = StatusPedidoEnum::ENTREGUE->value;

        return User::select('users.id', 'users.name', 'users.email') // CORREÇÃO: Selecionar apenas campos necessários, não 'users.*'
            ->selectRaw('COUNT(pedidos.id) as total_pedidos')
            ->selectRaw('SUM(pedidos.total) as total_gasto')
            ->join('pedidos', 'users.id', '=', 'pedidos.user_id')
            ->where('pedidos.status', $statusEntregue)
            ->groupBy('users.id', 'users.name', 'users.email')
            ->orderBy('total_gasto', 'desc')
            ->limit(5)
            ->get()
            ->toArray();
    }
}