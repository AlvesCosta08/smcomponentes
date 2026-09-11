<?php

namespace App\Services;

use App\Models\Produto;
use Illuminate\Support\Collection;

class ProductService
{
    public function list(array $filters = []): Collection
    {
        $query = Produto::query();
        if (isset($filters['categoria'])) {
            $query->where('categoria_id', $filters['categoria']);
        }
        if (isset($filters['search'])) {
            $query->where('descricao', 'like', "%{$filters['search']}%");
        }
        if (isset($filters['active'])) {
            $query->where('ativo', $filters['active']);
        }
        return $query->get();
    }

    public function findById(int $id): ?Produto
    {
        return Produto::find($id);
    }

    public function findBySlug(string $slug): ?Produto
    {
        return Produto::where('slug', $slug)->first();
    }

    public function findByReferencia(string $referencia): ?Produto
    {
        return Produto::where('referencia', $referencia)->first();
    }

    public function create(array $data): Produto
    {
        return Produto::create($data);
    }

    public function update(int $id, array $data): Produto
    {
        $produto = $this->findById($id);
        $produto->update($data);
        return $produto;
    }

    public function delete(int $id): bool
    {
        return Produto::destroy($id);
    }

    public function restore(int $id): bool
    {
        $produto = Produto::withTrashed()->find($id);
        if ($produto) {
            return $produto->restore();
        }
        return false;
    }

    public function ajustarEstoque(int $id, int $quantidade, string $tipo = 'adicionar'): Produto
    {
        $produto = $this->findById($id);
        if ($tipo === 'adicionar') {
            $produto->quantidade += $quantidade;
        } else {
            $produto->quantidade -= $quantidade;
        }
        $produto->save();
        return $produto;
    }

    public function getProdutosPorCategoria(int $categoriaId): Collection
    {
        return Produto::where('categoria_id', $categoriaId)->get();
    }

    public function getProdutosPorTermo(string $termo): Collection
    {
        return Produto::where('descricao', 'like', "%{$termo}%")
                      ->orWhere('referencia', 'like', "%{$termo}%")
                      ->get();
    }

    public function getDestaques(): Collection
    {
        return Produto::emDestaque()->get();
    }

    public function getOfertas(): Collection
    {
        return Produto::ofertas()->get();
    }

    public function getNovos(): Collection
    {
        return Produto::novos()->get();
    }

    public function getBaixoEstoque(int $limite = 5): Collection
    {
        return Produto::baixoEstoque($limite)->get();
    }

    public function getEstatisticas(): array
    {
        return [
            'total' => Produto::count(),
            'ativos' => Produto::where('ativo', true)->count(),
            'destaques' => Produto::where('destaque', true)->count(),
            'baixo_estoque' => Produto::baixoEstoque(5)->count(),
            'indisponiveis' => Produto::where('ativo', true)->where('quantidade', '<=', 0)->count(),
        ];
    }

    public function listarCategorias(): Collection
    {
        return Produto::distinct()->pluck('categoria_id');
    }

    public function contarProdutosPorCategoria(): Collection
    {
        return Produto::selectRaw('categoria_id, count(*) as total')
                      ->groupBy('categoria_id')
                      ->get();
    }

    public function incrementarVisualizacoes(int $id): void
    {
        $produto = $this->findById($id);
        if ($produto) {
            $produto->increment('visualizacoes');
            $produto->ultima_visualizacao = now();
            $produto->save();
        }
    }
}