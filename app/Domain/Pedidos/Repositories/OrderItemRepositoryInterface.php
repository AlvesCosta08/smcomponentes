<?php

namespace App\Domain\Pedidos\Repositories;

use App\Models\PedidoItem;
use Illuminate\Database\Eloquent\Collection;

interface OrderItemRepositoryInterface
{
    public function create(array $data): PedidoItem;
    public function createMany(array $items): Collection;
    public function getByOrderId(int $orderId): Collection;
}