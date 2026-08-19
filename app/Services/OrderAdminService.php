<?php
// app/Services/OrderAdminService.php

namespace App\Services;

use App\Models\Pedido;
use App\Repositories\PedidoRepository;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OrderAdminService
{
    public function __construct(
        protected PedidoRepository $repository,
        protected StockService $stockService
    ) {}

    /**
     * Listar pedidos com filtros
     */
    public function listOrders(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = Pedido::with('user');

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['data_inicio'])) {
            $query->whereDate('created_at', '>=', $filters['data_inicio']);
        }

        if (!empty($filters['data_fim'])) {
            $query->whereDate('created_at', '<=', $filters['data_fim']);
        }

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function($q) use ($search) {
                $q->where('numero_pedido', 'LIKE', "%{$search}%")
                  ->orWhereHas('user', function($user) use ($search) {
                      $user->where('name', 'LIKE', "%{$search}%")
                           ->orWhere('email', 'LIKE', "%{$search}%");
                  });
            });
        }

        return $query->orderBy('created_at', 'desc')->paginate($perPage);
    }

    /**
     * Obter detalhes do pedido
     */
    public function getOrderDetails(int $id): ?Pedido
    {
        return Pedido::with(['user', 'itens.produto'])->find($id);
    }

    /**
     * Atualizar status do pedido
     */
    public function updateStatus(Pedido $pedido, string $novoStatus): bool
    {
        try {
            DB::beginTransaction();

            $statusAnterior = $pedido->status;

            // Validar transição
            $this->validateStatusTransition($pedido, $novoStatus);

            // Lógica específica por status
            $this->handleStatusChange($pedido, $novoStatus, $statusAnterior);

            // Salvar
            $pedido->save();

            DB::commit();

            Log::info('Status do pedido atualizado', [
                'pedido_id' => $pedido->id,
                'status_anterior' => $statusAnterior,
                'novo_status' => $novoStatus,
                'usuario_id' => auth()->id()
            ]);

            return true;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao atualizar status: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Validar transição de status
     */
    protected function validateStatusTransition(Pedido $pedido, string $novoStatus): void
    {
        $statusAnterior = $pedido->status;

        // Não permitir alterar pedidos já entregues
        if ($statusAnterior === 'entregue') {
            throw new \Exception('Pedido já entregue não pode ser alterado.');
        }

        // Não permitir alterar pedidos cancelados
        if ($statusAnterior === 'cancelado') {
            throw new \Exception('Pedido cancelado não pode ser alterado.');
        }

        // Verificar se pode cancelar
        if ($novoStatus === 'cancelado' && !$pedido->podeCancelar()) {
            throw new \Exception('Este pedido não pode ser cancelado.');
        }
    }

    /**
     * Lidar com mudança de status
     */
    protected function handleStatusChange(Pedido $pedido, string $novoStatus, string $statusAnterior): void
    {
        // Cancelar: restaurar estoque
        if ($novoStatus === 'cancelado' && $statusAnterior !== 'cancelado') {
            foreach ($pedido->itens as $item) {
                $produto = $item->produto;
                if ($produto) {
                    $this->stockService->releaseStock($produto, $item->quantidade);
                }
            }
        }

        // Confirmar pagamento
        if ($novoStatus === 'pago' && $statusAnterior === 'pendente') {
            $pedido->data_pagamento = now();
            $pedido->status_pagamento = 'pago';
        }

        // Enviar
        if ($novoStatus === 'enviado' && $statusAnterior !== 'enviado') {
            $pedido->data_envio = now();
        }

        // Entregar
        if ($novoStatus === 'entregue' && $statusAnterior !== 'entregue') {
            $pedido->data_entrega = now();
        }

        $pedido->status = $novoStatus;
    }

    /**
     * Excluir pedido (apenas cancelados)
     */
    public function deleteOrder(Pedido $pedido): bool
    {
        if ($pedido->status !== 'cancelado') {
            throw new \Exception('Apenas pedidos cancelados podem ser excluídos.');
        }

        try {
            DB::beginTransaction();

            $pedido->itens()->delete();
            $pedido->delete();

            DB::commit();

            Log::info('Pedido excluído', [
                'pedido_id' => $pedido->id,
                'usuario_id' => auth()->id()
            ]);

            return true;

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Obter estatísticas para o dashboard
     */
    public function getStats(): array
    {
        return [
            'total' => Pedido::count(),
            'faturado' => Pedido::where('status', 'entregue')->sum('total') ?? 0,
            'pendentes' => Pedido::where('status', 'pendente')->count(),
            'hoje' => Pedido::whereDate('created_at', today())->count(),
        ];
    }

    /**
     * Exportar pedidos para CSV
     */
    public function export(array $filters = []): \Illuminate\Database\Eloquent\Collection
    {
        $query = Pedido::with('user');

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['data_inicio'])) {
            $query->whereDate('created_at', '>=', $filters['data_inicio']);
        }

        if (!empty($filters['data_fim'])) {
            $query->whereDate('created_at', '<=', $filters['data_fim']);
        }

        return $query->orderBy('created_at', 'desc')->get();
    }

    /**
     * Relatório de vendas
     */
    public function getSalesReport(string $dataInicio, string $dataFim): array
    {
        $inicio = \Carbon\Carbon::parse($dataInicio)->startOfDay();
        $fim = \Carbon\Carbon::parse($dataFim)->endOfDay();

        $pedidos = Pedido::where('status', 'entregue')
            ->whereBetween('created_at', [$inicio, $fim])
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->get();

        $totalVendas = $pedidos->sum('total') ?? 0;
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
}