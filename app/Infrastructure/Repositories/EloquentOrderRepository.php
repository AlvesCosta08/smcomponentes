<?php

namespace App\Infrastructure\Repositories;

use App\Domain\Pedidos\Repositories\OrderRepositoryInterface;
use App\Models\Pedido;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class EloquentOrderRepository implements OrderRepositoryInterface
{
    public function create(array $data): Pedido
    {
        return Pedido::create($data);
    }

    public function findById(int $id): ?Pedido
    {
        return Pedido::with(['user', 'itens.produto'])->find($id);
    }

    public function findByNumero(string $numero): ?Pedido
    {
        return Pedido::with(['user', 'itens.produto'])
            ->where('numero_pedido', $numero)
            ->first();
    }

    public function update(Pedido $pedido, array $data): bool
    {
        return $pedido->update($data);
    }

    public function getUserOrders(int $userId, int $perPage = 15): LengthAwarePaginator
    {
        return Pedido::where('user_id', $userId)
            ->with(['itens.produto'])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    public function getPendingOrders(): Collection
    {
        return Pedido::where('status', 'pendente')
            ->with(['user', 'itens.produto'])
            ->get();
    }

    public function getOrdersByStatus(string $status, int $perPage = 15): LengthAwarePaginator
    {
        return Pedido::where('status', $status)
            ->with(['user', 'itens.produto'])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }
}