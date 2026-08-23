<?php

namespace App\Domain\Pedidos\Repositories;

use App\Models\Pedido;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface OrderRepositoryInterface
{
    public function create(array $data): Pedido;
    public function findById(int $id): ?Pedido;
    public function findByNumero(string $numero): ?Pedido;
    public function update(Pedido $pedido, array $data): bool;
    public function getUserOrders(int $userId, int $perPage = 15): LengthAwarePaginator;
    public function getPendingOrders(): Collection;
    public function getOrdersByStatus(string $status, int $perPage = 15): LengthAwarePaginator;
}