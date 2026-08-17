<?php
// app/Repositories/PedidoItemRepository.php

namespace App\Repositories;

use App\Models\PedidoItem;
use App\Repositories\Contracts\PedidoItemRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PedidoItemRepository extends BaseRepository implements PedidoItemRepositoryInterface
{
    /**
     * {@inheritdoc}
     */
    protected function model(): string
    {
        return PedidoItem::class;
    }

    /**
     * {@inheritdoc}
     */
    public function findByPedido(int $pedidoId): Collection
    {
        $this->newQuery();
        return $this->query->where('pedido_id', $pedidoId)->get();
    }

    /**
     * {@inheritdoc}
     */
    public function findByProduto(int $produtoId): Collection
    {
        $this->newQuery();
        return $this->query->where('produto_id', $produtoId)->get();
    }

    /**
     * {@inheritdoc}
     */
    public function findWithProdutos(int $pedidoId): Collection
    {
        $this->newQuery();
        return $this->query->where('pedido_id', $pedidoId)
            ->with('produto')
            ->get();
    }

    /**
     * {@inheritdoc}
     */
    public function createMany(array $items): Collection
    {
        try {
            $created = [];
            foreach ($items as $item) {
                $created[] = $this->create($item);
            }
            return new Collection($created);
        } catch (\Exception $e) {
            Log::error('Erro ao criar múltiplos itens', [
                'items' => $items,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function deleteByPedido(int $pedidoId): bool
    {
        try {
            return (bool) $this->model->where('pedido_id', $pedidoId)->delete();
        } catch (\Exception $e) {
            Log::error('Erro ao deletar itens do pedido', [
                'pedido_id' => $pedidoId,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getSubtotalByPedido(int $pedidoId): float
    {
        return $this->model->where('pedido_id', $pedidoId)->sum('subtotal') ?? 0;
    }

    /**
     * {@inheritdoc}
     */
    public function getTopProducts(int $limit = 10): Collection
    {
        return $this->model->select(
                'produto_id',
                DB::raw('COUNT(*) as total_pedidos'),
                DB::raw('SUM(quantidade) as total_vendidos'),
                DB::raw('SUM(subtotal) as total_faturado')
            )
            ->with('produto')
            ->groupBy('produto_id')
            ->orderBy('total_vendidos', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * {@inheritdoc}
     */
    public function getProductStats(int $produtoId): array
    {
        $stats = $this->model->where('produto_id', $produtoId)
            ->select(
                DB::raw('COUNT(*) as total_pedidos'),
                DB::raw('SUM(quantidade) as total_vendidos'),
                DB::raw('SUM(subtotal) as total_faturado'),
                DB::raw('AVG(quantidade) as media_por_pedido')
            )
            ->first();

        return [
            'total_pedidos' => $stats->total_pedidos ?? 0,
            'total_vendidos' => $stats->total_vendidos ?? 0,
            'total_faturado' => $stats->total_faturado ?? 0,
            'media_por_pedido' => $stats->media_por_pedido ?? 0,
        ];
    }

    /**
     * Sobrescrever create para registrar logs
     */
    public function create(array $data): PedidoItem
    {
        $item = parent::create($data);
        
        Log::info('Item do pedido criado', [
            'pedido_id' => $item->pedido_id,
            'produto_id' => $item->produto_id,
            'quantidade' => $item->quantidade,
            'subtotal' => $item->subtotal
        ]);

        return $item;
    }
}