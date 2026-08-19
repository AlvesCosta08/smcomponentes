<?php

namespace App\Observers;

use App\Models\Produto;
use Illuminate\Support\Str;

class ProdutoObserver
{
    public function creating(Produto $produto): void
    {
        $this->generateSlug($produto);
        $this->ensureDefaults($produto);
        $this->recalculatePrices($produto);
        $this->updateAvailability($produto);
    }

    public function updating(Produto $produto): void
    {
        if ($produto->isDirty('descricao')) {
            $this->generateSlug($produto);
        }
        
        if ($produto->isDirty(['quantidade', 'ativo'])) {
            $produto->atualizarDisponibilidade();
        }

        if ($produto->isDirty('quantidade')) {
            $produto->ultima_atualizacao_estoque = now();
        }

        if ($produto->isDirty(['valor_compra', 'margem_lucro', 'ipi'])) {
            $produto->recalcularPrecos();
        }
    }

    private function generateSlug(Produto $produto): void
    {
        if (empty($produto->slug)) {
            $produto->slug = Str::slug($produto->descricao . '-' . Str::random(6));
        }
    }

    private function ensureDefaults(Produto $produto): void
    {
        $produto->disponibilidade ??= 'DISPONIVEL';
        $produto->visualizacoes ??= 0;
        $produto->rating ??= 0;
        $produto->total_avaliacoes ??= 0;
    }

    private function recalculatePrices(Produto $produto): void
    {
        if (!empty($produto->valor_compra)) {
            $produto->recalcularPrecos();
        }
    }

    private function updateAvailability(Produto $produto): void
    {
        $produto->atualizarDisponibilidade();
    }
}