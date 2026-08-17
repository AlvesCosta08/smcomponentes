<?php
// app/Services/Contracts/StockServiceInterface.php

namespace App\Services\Contracts;

use App\Models\Produto;
use App\Exceptions\OutOfStockException;
use Illuminate\Database\Eloquent\Collection;

interface StockServiceInterface
{
    /**
     * Reservar estoque para um produto (com lock no banco)
     *
     * @param Produto $produto Produto a ter estoque reservado
     * @param int $quantidade Quantidade a reservar
     * @throws OutOfStockException
     */
    public function reserveStock(Produto $produto, int $quantidade): void;

    /**
     * Liberar estoque de um produto
     *
     * @param Produto $produto Produto a ter estoque liberado
     * @param int $quantidade Quantidade a liberar
     */
    public function releaseStock(Produto $produto, int $quantidade): void;

    /**
     * Validar se há estoque suficiente (sem reservar)
     *
     * @param Produto $produto Produto a verificar
     * @param int $quantidade Quantidade desejada
     * @return bool
     */
    public function validateStock(Produto $produto, int $quantidade): bool;

    /**
     * Validar estoque de múltiplos produtos
     *
     * @param array $itens [produto_id => ['quantidade' => X]]
     * @throws OutOfStockException
     */
    public function validateMultipleStock(array $itens): void;

    /**
     * Verificar produtos com estoque baixo (crítico)
     *
     * @param int $threshold Limite mínimo para considerar baixo
     * @return Collection
     */
    public function getLowStockProducts(int $threshold = 5): Collection;

    /**
     * Verificar produtos sem estoque
     *
     * @return Collection
     */
    public function getOutOfStockProducts(): Collection;

    /**
     * Ajustar estoque manualmente (com log)
     *
     * @param Produto $produto
     * @param int $quantidade Nova quantidade
     * @param string $motivo Motivo do ajuste
     * @return void
     */
    public function adjustStock(Produto $produto, int $quantidade, string $motivo): void;

    /**
     * Adicionar estoque
     *
     * @param Produto $produto
     * @param int $quantidade Quantidade a adicionar
     * @param string $motivo Motivo da adição
     */
    public function addStock(Produto $produto, int $quantidade, string $motivo = 'reposicao'): void;

    /**
     * Remover estoque
     *
     * @param Produto $produto
     * @param int $quantidade Quantidade a remover
     * @param string $motivo Motivo da remoção
     * @throws OutOfStockException
     */
    public function removeStock(Produto $produto, int $quantidade, string $motivo = 'venda'): void;

    /**
     * Verificar se o produto tem estoque
     *
     * @param Produto $produto
     * @return bool
     */
    public function hasStock(Produto $produto): bool;
}