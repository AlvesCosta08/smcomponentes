<?php
// app/Repositories/PedidoRepository.php

namespace App\Repositories;

use App\Models\Pedido;
use App\Repositories\Contracts\PedidoRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class PedidoRepository extends BaseRepository implements PedidoRepositoryInterface
{
    /**
     * {@inheritdoc}
     */
    protected function model(): string
    {
        return Pedido::class;
    }

    /**
     * {@inheritdoc}
     */
    public function findByUser(int $userId, int $perPage = 10): LengthAwarePaginator
    {
        $this->newQuery();
        return $this->query->where('user_id', $userId)
            ->with(['itens'])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * {@inheritdoc}
     */
    public function findByStatus(string $status, int $limit = null): Collection
    {
        $this->newQuery();
        $query = $this->query->where('status', $status)
            ->with(['user', 'itens']);

        if ($limit) {
            $query->limit($limit);
        }

        return $query->orderBy('created_at', 'desc')->get();
    }

    /**
     * {@inheritdoc}
     */
    public function findByDateRange(string $startDate, string $endDate): Collection
    {
        $this->newQuery();
        return $this->query->whereBetween('created_at', [
            Carbon::parse($startDate)->startOfDay(),
            Carbon::parse($endDate)->endOfDay()
        ])->with(['user', 'itens'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * {@inheritdoc}
     */
    public function findByNumero(string $numero): ?Pedido
    {
        $this->newQuery();
        return $this->query->where('numero_pedido', $numero)->first();
    }

    /**
     * {@inheritdoc}
     */
    public function getPendentes(int $limit = null): Collection
    {
        return $this->findByStatus('pendente', $limit);
    }

    /**
     * {@inheritdoc}
     */
    public function getTodayOrders(): Collection
    {
        $this->newQuery();
        return $this->query->whereDate('created_at', today())
            ->with(['user', 'itens'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * {@inheritdoc}
     */
    public function getTotalRevenue(?string $status = null, ?string $startDate = null, ?string $endDate = null): float
    {
        $this->newQuery();

        if ($status) {
            $this->query->where('status', $status);
        }

        if ($startDate) {
            $this->query->whereDate('created_at', '>=', Carbon::parse($startDate));
        }

        if ($endDate) {
            $this->query->whereDate('created_at', '<=', Carbon::parse($endDate));
        }

        return $this->query->sum('total') ?? 0;
    }

    /**
     * {@inheritdoc}
     */
    public function getAverageTicket(?string $startDate = null, ?string $endDate = null): float
    {
        $this->newQuery();

        if ($startDate) {
            $this->query->whereDate('created_at', '>=', Carbon::parse($startDate));
        }

        if ($endDate) {
            $this->query->whereDate('created_at', '<=', Carbon::parse($endDate));
        }

        $average = $this->query->where('status', 'entregue')
            ->avg('total');

        return $average ?? 0;
    }

    /**
     * {@inheritdoc}
     */
    public function getCountByStatus(): array
    {
        $statuses = ['pendente', 'pago', 'processando', 'enviado', 'entregue', 'cancelado'];
        $counts = [];

        foreach ($statuses as $status) {
            $counts[$status] = $this->count(['status' => $status]);
        }

        return $counts;
    }

    /**
     * {@inheritdoc}
     */
    public function getSalesByDay(int $days = 30): array
    {
        $startDate = Carbon::now()->subDays($days)->startOfDay();
        $endDate = Carbon::now()->endOfDay();

        $results = $this->model->whereBetween('created_at', [$startDate, $endDate])
            ->where('status', 'entregue')
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as total_orders'),
                DB::raw('SUM(total) as total_revenue')
            )
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->get();

        return $results->toArray();
    }

    /**
     * {@inheritdoc}
     */
    public function getTopCustomers(int $limit = 10): Collection
    {
        return $this->model->where('status', 'entregue')
            ->select(
                'user_id',
                DB::raw('COUNT(*) as total_pedidos'),
                DB::raw('SUM(total) as total_gasto')
            )
            ->with('user')
            ->groupBy('user_id')
            ->orderBy('total_gasto', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * {@inheritdoc}
     */
    public function generateNumero(): string
    {
        $prefix = 'SM';
        $year = date('Y');
        $month = date('m');
        $day = date('d');

        // Buscar último número do dia
        $last = $this->model->whereDate('created_at', today())
            ->orderBy('id', 'desc')
            ->first();

        if ($last) {
            $lastNumber = (int) substr($last->numero_pedido, -4);
            $sequence = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $sequence = '0001';
        }

        return "{$prefix}{$year}{$month}{$day}{$sequence}";
    }

    /**
     * {@inheritdoc}
     */
    public function updateStatus(int $id, string $status, array $extra = []): Pedido
    {
        $pedido = $this->findOrFail($id);

        $data = ['status' => $status];

        // Atualizar campos específicos baseado no status
        if ($status === 'pago' && !$pedido->data_pagamento) {
            $data['data_pagamento'] = now();
            $data['status_pagamento'] = 'approved';
        }

        if ($status === 'enviado' && !$pedido->data_envio) {
            $data['data_envio'] = now();
        }

        if ($status === 'entregue' && !$pedido->data_entrega) {
            $data['data_entrega'] = now();
        }

        if ($status === 'cancelado') {
            $data['status_pagamento'] = 'cancelled';
        }

        // Merge com dados extras
        $data = array_merge($data, $extra);

        return $this->update($id, $data);
    }

    /**
     * {@inheritdoc}
     */
    public function getFilteredOrders(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $this->newQuery();

        if (!empty($filters['status'])) {
            $this->query->where('status', $filters['status']);
        }

        if (!empty($filters['data_inicio'])) {
            $this->query->whereDate('created_at', '>=', $filters['data_inicio']);
        }

        if (!empty($filters['data_fim'])) {
            $this->query->whereDate('created_at', '<=', $filters['data_fim']);
        }

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $this->query->where(function($q) use ($search) {
                $q->where('numero_pedido', 'LIKE', "%{$search}%")
                    ->orWhereHas('user', function($user) use ($search) {
                        $user->where('name', 'LIKE', "%{$search}%")
                            ->orWhere('email', 'LIKE', "%{$search}%");
                    });
            });
        }

        $this->query->with(['user', 'itens']);
        $this->query->orderBy('created_at', 'desc');

        return $this->query->paginate($perPage);
    }

    /**
     * {@inheritdoc}
     */
    public function getSalesReport(string $startDate, string $endDate): array
    {
        $inicio = Carbon::parse($startDate)->startOfDay();
        $fim = Carbon::parse($endDate)->endOfDay();

        $pedidos = $this->model->where('status', 'entregue')
            ->whereBetween('created_at', [$inicio, $fim])
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->get();

        $totalVendas = $pedidos->sum('total');
        $totalPedidos = $pedidos->count();
        $mediaTicket = $totalPedidos > 0 ? $totalVendas / $totalPedidos : 0;

        // Vendas por dia
        $vendasPorDia = $pedidos->groupBy(function($item) {
            return $item->created_at->format('Y-m-d');
        })->map(function($group) {
            return [
                'data' => $group->first()->created_at->format('d/m/Y'),
                'total' => $group->sum('total'),
                'quantidade' => $group->count()
            ];
        })->values()->toArray();

        usort($vendasPorDia, function($a, $b) {
            return strtotime($a['data']) - strtotime($b['data']);
        });

        return [
            'pedidos' => $pedidos,
            'total_vendas' => $totalVendas,
            'total_pedidos' => $totalPedidos,
            'media_ticket' => $mediaTicket,
            'vendas_por_dia' => $vendasPorDia,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function canBeCancelled(int $id): bool
    {
        $pedido = $this->find($id);
        if (!$pedido) {
            return false;
        }

        return in_array($pedido->status, ['pendente', 'pago', 'processando']);
    }

    /**
     * {@inheritdoc}
     */
    public function findWithItems(int $id): ?Pedido
    {
        $this->newQuery();
        return $this->query->with(['user', 'itens.produto'])
            ->find($id);
    }
}