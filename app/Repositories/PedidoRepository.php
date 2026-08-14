<?php
// app/Repositories/PedidoRepository.php

namespace App\Repositories;

use App\Models\Pedido;
use Illuminate\Pagination\LengthAwarePaginator;

class PedidoRepository
{
    public function create(array $data): Pedido
    {
        return Pedido::create($data);
    }

    public function find(int $id): ?Pedido
    {
        return Pedido::find($id);
    }

    public function findOrFail(int $id): Pedido
    {
        return Pedido::findOrFail($id);
    }

    public function update(int $id, array $data): Pedido
    {
        $pedido = $this->findOrFail($id);
        $pedido->update($data);
        return $pedido->fresh();
    }

    public function delete(int $id): bool
    {
        return Pedido::destroy($id) > 0;
    }

    public function getByUser(int $userId, int $perPage = 15): LengthAwarePaginator
    {
        return Pedido::where('user_id', $userId)
            ->with('itens')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    public function getByStatus(string $status, int $perPage = 15): LengthAwarePaginator
    {
        return Pedido::where('status', $status)
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    public function getStats(): array
    {
        return [
            'total' => Pedido::count(),
            'pendentes' => Pedido::where('status', 'pendente')->count(),
            'confirmados' => Pedido::where('status', 'confirmado')->count(),
            'preparando' => Pedido::where('status', 'preparando')->count(),
            'enviados' => Pedido::where('status', 'enviado')->count(),
            'entregues' => Pedido::where('status', 'entregue')->count(),
            'cancelados' => Pedido::where('status', 'cancelado')->count(),
            'total_vendas' => Pedido::where('status', 'entregue')->sum('total'),
        ];
    }
}