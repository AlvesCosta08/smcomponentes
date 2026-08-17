<?php
// app/Repositories/Contracts/PedidoItemRepositoryInterface.php

namespace App\Repositories\Contracts;

use App\Models\PedidoItem;
use Illuminate\Database\Eloquent\Collection;

interface PedidoItemRepositoryInterface extends RepositoryInterface
{
    /**
     * Buscar itens por pedido
     *
     * @param int $pedidoId
     * @return Collection
     */
    public function findByPedido(int $pedidoId): Collection;

    /**
     * Buscar itens por produto
     *
     * @param int $produtoId
     * @return Collection
     */
    public function findByProduto(int $produtoId): Collection;

    /**
     * Buscar itens por pedido com produtos
     *
     * @param int $pedidoId
     * @return Collection
     */
    public function findWithProdutos(int $pedidoId): Collection;

    /**
     * Criar múltiplos itens de uma vez
     *
     * @param array $items
     * @return Collection
     */
    public function createMany(array $items): Collection;

    /**
     * Deletar itens por pedido
     *
     * @param int $pedidoId
     * @return bool
     */
    public function deleteByPedido(int $pedidoId): bool;

    /**
     * Calcular subtotal do pedido
     *
     * @param int $pedidoId
     * @return float
     */
    public function getSubtotalByPedido(int $pedidoId): float;

    /**
     * Obter produtos mais vendidos
     *
     * @param int $limit
     * @return Collection
     */
    public function getTopProducts(int $limit = 10): Collection;

    /**
     * Obter estatísticas de vendas por produto
     *
     * @param int $produtoId
     * @return array
     */
    public function getProductStats(int $produtoId): array;
}