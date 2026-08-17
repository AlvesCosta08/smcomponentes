<?php
// app/Services/StockService.php

namespace App\Services;

use App\Models\Produto;
use App\Exceptions\OutOfStockException;
use App\Repositories\Contracts\ProdutoRepositoryInterface;
use App\Services\Contracts\StockServiceInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class StockService implements StockServiceInterface
{
    public function __construct(
        protected ProdutoRepositoryInterface $produtoRepository
    ) {}

    /**
     * {@inheritdoc}
     */
    public function reserveStock(Produto $produto, int $quantidade): void
    {
        DB::transaction(function() use ($produto, $quantidade) {
            // Lock for update - previne race condition
            $produto = DB::table('produtos')
                ->where('id', $produto->id)
                ->lockForUpdate()
                ->first();

            if (!$produto) {
                throw new \Exception("Produto não encontrado: {$produto->id}");
            }

            if ($produto->quantidade < $quantidade) {
                throw new OutOfStockException(
                    "Estoque insuficiente: {$produto->descricao}. " .
                    "Disponível: {$produto->quantidade}, Solicitado: {$quantidade}"
                );
            }

            // Atualizar usando query builder para evitar race condition
            DB::table('produtos')
                ->where('id', $produto->id)
                ->decrement('quantidade', $quantidade);

            Log::info('Estoque reservado', [
                'produto_id' => $produto->id,
                'quantidade' => $quantidade,
                'estoque_atual' => $produto->quantidade - $quantidade
            ]);
        });
    }

    /**
     * {@inheritdoc}
     */
    public function releaseStock(Produto $produto, int $quantidade): void
    {
        DB::transaction(function() use ($produto, $quantidade) {
            DB::table('produtos')
                ->where('id', $produto->id)
                ->increment('quantidade', $quantidade);

            Log::info('Estoque liberado', [
                'produto_id' => $produto->id,
                'quantidade' => $quantidade
            ]);
        });
    }

    /**
     * {@inheritdoc}
     */
    public function validateStock(Produto $produto, int $quantidade): bool
    {
        return $produto->fresh()->quantidade >= $quantidade;
    }

    /**
     * {@inheritdoc}
     */
    public function validateMultipleStock(array $itens): void
    {
        foreach ($itens as $produtoId => $item) {
            $produto = $this->produtoRepository->find($produtoId);
            
            if (!$produto) {
                throw new \Exception("Produto não encontrado: {$produtoId}");
            }

            $quantidade = $item['quantidade'] ?? 1;
            
            if (!$this->validateStock($produto, $quantidade)) {
                throw new OutOfStockException(
                    "Produto '{$produto->descricao}' não tem estoque suficiente. " .
                    "Disponível: {$produto->quantidade}, Solicitado: {$quantidade}"
                );
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getLowStockProducts(int $threshold = 5): Collection
    {
        return $this->produtoRepository->getLowStock($threshold);
    }

    /**
     * {@inheritdoc}
     */
    public function getOutOfStockProducts(): Collection
    {
        return $this->produtoRepository->getOutOfStock();
    }

    /**
     * {@inheritdoc}
     */
    public function adjustStock(Produto $produto, int $quantidade, string $motivo): void
    {
        DB::transaction(function() use ($produto, $quantidade, $motivo) {
            $produto->quantidade = $quantidade;
            $produto->save();

            Log::info('Estoque ajustado manualmente', [
                'produto_id' => $produto->id,
                'nova_quantidade' => $quantidade,
                'motivo' => $motivo
            ]);
        });
    }

    /**
     * {@inheritdoc}
     */
    public function addStock(Produto $produto, int $quantidade, string $motivo = 'reposicao'): void
    {
        DB::transaction(function() use ($produto, $quantidade, $motivo) {
            $produto->quantidade += $quantidade;
            $produto->save();

            Log::info('Estoque adicionado', [
                'produto_id' => $produto->id,
                'quantidade' => $quantidade,
                'motivo' => $motivo,
                'novo_total' => $produto->quantidade
            ]);
        });
    }

    /**
     * {@inheritdoc}
     */
    public function removeStock(Produto $produto, int $quantidade, string $motivo = 'venda'): void
    {
        DB::transaction(function() use ($produto, $quantidade, $motivo) {
            if ($produto->quantidade < $quantidade) {
                throw new OutOfStockException(
                    "Estoque insuficiente para remover {$quantidade} unidades. " .
                    "Disponível: {$produto->quantidade}"
                );
            }

            $produto->quantidade -= $quantidade;
            $produto->save();

            Log::info('Estoque removido', [
                'produto_id' => $produto->id,
                'quantidade' => $quantidade,
                'motivo' => $motivo,
                'novo_total' => $produto->quantidade
            ]);
        });
    }

    /**
     * {@inheritdoc}
     */
    public function hasStock(Produto $produto): bool
    {
        return $produto->quantidade > 0;
    }
}