<?php

namespace App\Infrastructure\Repositories;

use App\Domain\Pedidos\Repositories\PedidoRepositoryInterface;
use App\Models\Pedido;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class EloquentPedidoRepository implements PedidoRepositoryInterface
{
    public function create(array $data): Pedido
    {
        return DB::transaction(function () use ($data) {
            $itemsData = $data['items'] ?? [];
            unset($data['items']); // Remove os itens dos dados principais do pedido

            // 1. Cria o pedido
            $pedido = Pedido::create($data);

            // 2. Cria os itens do pedido (preservando nome e imagem históricos)
            foreach ($itemsData as $item) {
                $pedido->itens()->create([
                    'produto_id' => $item['produto_id'],
                    'quantidade' => $item['quantidade'],
                    'preco_unitario' => $item['preco_unitario'],
                    'preco_promocional' => $item['preco_promocional'] ?? null,
                    'subtotal' => $item['quantidade'] * ($item['preco_promocional'] ?? $item['preco_unitario']),
                    'nome_produto' => $item['nome_produto'],
                    'imagem_produto' => $item['imagem_produto'] ?? null,
                ]);
            }

            // 3. Recalcula e salva os totais finais
            $pedido->calcularTotal();

            return $pedido->load('itens', 'user');
        });
.
    public function findById(int $id): ?Pedido
    {
        return Pedido::with(['itens.produto', 'user'])->find($id);
    }

    public function findByNumero(string $numero): ?Pedido
    {
        return Pedido::with(['itens.produto', 'user'])->where('numero_pedido', $numero)->first();
    }

    public function update(Pedido $pedido, array $data): bool
    {
        return $pedido->update($data);
    }

    public function getUserOrders(int $userId, int $perPage = 15): LengthAwarePaginator
    {
        return Pedido::where('user_id', $userId)
            ->with('itens')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    public function getFiltered(array $filters, int $perPage = 15): LengthAwarePaginator
    {
        $query = Pedido::with(['user', 'itens']);

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        if (!empty($filters['search'])) {
            $query->where('numero_pedido', 'LIKE', "%{$filters['search']}%")
                  ->orWhereHas('user', function ($q) use ($filters) {
                      $q->where('name', 'LIKE', "%{$filters['search']}%")
                        ->orWhere('email', 'LIKE', "%{$filters['search']}%");
                  });
        }

        return $query->orderBy('created_at', 'desc')->paginate($perPage);
    }

    public function getPendingOrders(): Collection
    {
        return Pedido::where('status', 'pendente')
            ->with('user')
            ->orderBy('created_at', 'asc')
            ->get();
    }
}