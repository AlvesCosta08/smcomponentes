<?php
// app/Services/StockService.php

namespace App\Services;

use App\Models\Produto;
use App\Exceptions\OutOfStockException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class StockService
{
    /**
     * Validar se o produto tem estoque suficiente
     */
    public function validateStock(Produto $produto, int $quantity): bool
    {
        if (!$produto->temEstoque($quantity)) {
            throw new OutOfStockException(
                "Produto '{$produto->descricao}' não tem estoque suficiente. " .
                "Disponível: {$produto->quantidade}, Solicitado: {$quantity}"
            );
        }
        
        return true;
    }

    /**
     * Reservar estoque (reduzir)
     */
    public function reserveStock(Produto $produto, int $quantity, bool $throwException = true): bool
    {
        if ($throwException) {
            $this->validateStock($produto, $quantity);
        } else {
            if (!$produto->temEstoque($quantity)) {
                Log::warning('Tentativa de reserva sem estoque', [
                    'produto_id' => $produto->id,
                    'quantidade' => $quantity,
                    'estoque_atual' => $produto->quantidade
                ]);
                return false;
            }
        }

        try {
            DB::beginTransaction();
            
            $produto->reduzirEstoque($quantity);
            
            // Registrar log de movimentação
            Log::info('Estoque reservado', [
                'produto_id' => $produto->id,
                'quantidade' => $quantity,
                'estoque_restante' => $produto->quantidade,
                'usuario_id' => auth()->id()
            ]);
            
            DB::commit();
            return true;
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao reservar estoque: ' . $e->getMessage(), [
                'produto_id' => $produto->id,
                'quantidade' => $quantity
            ]);
            throw $e;
        }
    }

    /**
     * Liberar estoque (aumentar)
     */
    public function releaseStock(Produto $produto, int $quantity): bool
    {
        try {
            DB::beginTransaction();
            
            $produto->aumentarEstoque($quantity);
            
            Log::info('Estoque liberado', [
                'produto_id' => $produto->id,
                'quantidade' => $quantity,
                'novo_estoque' => $produto->quantidade,
                'usuario_id' => auth()->id()
            ]);
            
            DB::commit();
            return true;
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao liberar estoque: ' . $e->getMessage(), [
                'produto_id' => $produto->id,
                'quantidade' => $quantity
            ]);
            throw $e;
        }
    }

    /**
     * Verificar múltiplos produtos de uma vez
     */
    public function validateMultipleStock(array $items): array
    {
        $errors = [];

        foreach ($items as $item) {
            $produto = Produto::find($item['produto_id']);
            
            if (!$produto) {
                $errors[] = "Produto ID {$item['produto_id']} não encontrado";
                continue;
            }

            if (!$produto->temEstoque($item['quantidade'])) {
                $errors[] = "Produto '{$produto->descricao}' tem apenas {$produto->quantidade} unidades disponíveis";
            }
        }

        return $errors;
    }

    /**
     * Verificar se há produtos com baixo estoque
     */
    public function getLowStockProducts(int $threshold = 5): \Illuminate\Database\Eloquent\Collection
    {
        return Produto::where('quantidade', '<=', $threshold)
            ->where('ativo', true)
            ->orderBy('quantidade', 'asc')
            ->get();
    }

    /**
     * Verificar produtos com estoque crítico (0 ou negativo)
     */
    public function getCriticalStockProducts(): \Illuminate\Database\Eloquent\Collection
    {
        return Produto::where('quantidade', '<=', 0)
            ->where('ativo', true)
            ->orderBy('quantidade', 'asc')
            ->get();
    }
}