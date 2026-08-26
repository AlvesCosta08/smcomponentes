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
            $query->where('categoria', $filters['categoria']);
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
        $product = $this->findById($id);
        $product->update($data);
        return $product;
    }

    public function delete(int $id): bool
    {
        return Produto::destroy($id);
    }

    public function restore(int $id): bool
    {
        $product = Produto::withTrashed()->find($id);
        if ($product) {
            return $product->restore();
        }
        return false;
    }

    public function ajustarEstoque(int $id, int $quantidade, string $tipo = 'adicionar'): Produto
    {
        $product = $this->findById($id);
        
        if ($tipo === 'adicionar') {
            $product->quantidade += $quantidade;
        } else {
            $product->quantidade -= $quantidade;
        }
        
        $product->save();
        return $product;
    }

    public function getProdutosPorCategoria(string $categoria): Collection
    {
        return Produto::where('categoria', $categoria)->get();
    }

    public function getProdutosPorTermo(string $termo): Collection
    {
        return Produto::where('descricao', 'like', "%{$termo}%")
            ->orWhere('categoria', 'like', "%{$termo}%")
            ->get();
    }

    public function getDestaques(): Collection
    {
        return Produto::where('destaque', true)
            ->where('ativo', true)
            ->where('quantidade', '>', 0)
            ->get();
    }

    public function getOfertas(): Collection
    {
        return Produto::whereNotNull('preco_promocional')
            ->where('preco_promocional', '>', 0)
            ->where('ativo', true)
            ->get();
    }

    public function getNovos(): Collection
    {
        return Produto::where('novo', true)
            ->where('ativo', true)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function getBaixoEstoque(int $limite = 5): Collection
    {
        return Produto::where('quantidade', '<=', $limite)
            ->where('quantidade', '>', 0)
            ->where('ativo', true)
            ->orderBy('quantidade', 'asc')
            ->get();
    }

    public function getEstatisticas(): array
    {
        return [
            'total' => Produto::count(),
            'ativos' => Produto::where('ativo', true)->count(),
            'destaques' => Produto::where('destaque', true)->count(),
            'baixo_estoque' => Produto::where('quantidade', '<=', 5)
                ->where('quantidade', '>', 0)
                ->count(),
            'indisponiveis' => Produto::where('quantidade', '<=', 0)->count(),
        ];
    }

    public function listarCategorias(): Collection
    {
        return Produto::distinct()->pluck('categoria');
    }

    public function contarProdutosPorCategoria(): Collection
    {
        return Produto::selectRaw('categoria, count(*) as total')
            ->groupBy('categoria')
            ->get();
    }

    public function incrementarVisualizacoes(int $id): void
    {
        $product = $this->findById($id);
        if ($product) {
            $product->increment('visualizacoes');
            $product->ultima_visualizacao = now();
            $product->save();
        }
    }
}