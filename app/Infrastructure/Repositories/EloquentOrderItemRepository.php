<?php

namespace App\Infrastructure\Repositories;

use App\Domain\Pedidos\Repositories\OrderItemRepositoryInterface;
use App\Models\PedidoItem;
use Illuminate\Database\Eloquent\Collection;

class EloquentOrderItemRepository implements OrderItemRepositoryInterface
{
    public function create(array $data): PedidoItem
    {
        return PedidoItem::create($data);
    }

    public function createMany(array $items): Collection
    {
        $createdItems = new Collection();
        
        foreach ($items as $itemData) {
            $createdItems->push(PedidoItem::create($itemData));
        }

        return $createdItems;
    }

    public function getByOrderId(int $orderId): Collection
    {
        return PedidoItem::where('pedido_id', $orderId)
            ->with('produto')
            ->get();
    }
}