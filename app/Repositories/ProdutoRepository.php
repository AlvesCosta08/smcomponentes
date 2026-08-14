<?php
// app/Repositories/ProdutoRepository.php

namespace App\Repositories;

use App\Models\Produto;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class ProdutoRepository
{
    /**
     * Encontrar produto por ID
     */
    public function find(int $id): ?Produto
    {
        return Produto::find($id);
    }

    /**
     * Encontrar produto por ID ou lançar exceção
     */
    public function findOrFail(int $id): Produto
    {
        return Produto::findOrFail($id);
    }

    /**
     * Encontrar produto por slug
     */
    public function findBySlug(string $slug): ?Produto
    {
        return Produto::where('slug', $slug)->first();
    }

    /**
     * Encontrar produto por referência
     */
    public function findByReference(string $referencia): ?Produto
    {
        return Produto::where('referencia', $referencia)->first();
    }

    /**
     * Criar novo produto
     */
    public function create(array $data): Produto
    {
        if (!isset($data['ipi']) || $data['ipi'] === null) {
            $data['ipi'] = 9.75;
        }

        if (!isset($data['disponibilidade'])) {
            $data['disponibilidade'] = 'DISPONÍVEL';
        }

        if (!isset($data['ativo'])) {
            $data['ativo'] = true;
        }

        return Produto::create($data);
    }

    /**
     * Atualizar produto
     */
    public function update(int $id, array $data): Produto
    {
        $produto = $this->findOrFail($id);
        
        if (!isset($data['ipi']) || $data['ipi'] === null) {
            $data['ipi'] = 9.75;
        }
        
        $produto->update($data);
        return $produto->fresh();
    }

    /**
     * Deletar produto (soft delete)
     */
    public function delete(int $id): bool
    {
        $produto = $this->findOrFail($id);
        return $produto->delete();
    }

    /**
     * Restaurar produto deletado
     */
    public function restore(int $id): bool
    {
        $produto = Produto::withTrashed()->findOrFail($id);
        return $produto->restore();
    }

    /**
     * Deletar permanentemente
     */
    public function forceDelete(int $id): bool
    {
        $produto = Produto::withTrashed()->findOrFail($id);
        return $produto->forceDelete();
    }

    /**
     * Obter produtos com filtros (paginação)
     */
    public function getProdutosComFiltros(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = Produto::query();

        // Filtro por categoria
        if (!empty($filters['categoria'])) {
            $query->where('categoria', $filters['categoria']);
        }

        // Filtro por ativo
        if (isset($filters['ativo']) && $filters['ativo'] !== '') {
            $query->where('ativo', (bool) $filters['ativo']);
        }

        // Filtro por estoque
        if (isset($filters['estoque'])) {
            if ($filters['estoque'] === 'com_estoque') {
                $query->where('quantidade', '>', 0);
            } elseif ($filters['estoque'] === 'sem_estoque') {
                $query->where('quantidade', '<=', 0);
            } elseif ($filters['estoque'] === 'baixo_estoque') {
                $query->where('quantidade', '<=', 5)->where('quantidade', '>', 0);
            }
        }

        // Filtro por destaque
        if (isset($filters['destaque']) && $filters['destaque'] !== '') {
            $query->where('destaque', (bool) $filters['destaque']);
        }

        // Filtro por promoção
        if (isset($filters['promocao']) && $filters['promocao'] !== '') {
            if ($filters['promocao'] === 'com_promocao') {
                $query->whereNotNull('preco_promocional')->where('preco_promocional', '>', 0);
            } else {
                $query->whereNull('preco_promocional')->orWhere('preco_promocional', '<=', 0);
            }
        }

        // Filtro por novo
        if (isset($filters['novo']) && $filters['novo'] !== '') {
            $query->where('novo', (bool) $filters['novo']);
        }

        // Filtro por mais_vendido
        if (isset($filters['mais_vendido']) && $filters['mais_vendido'] !== '') {
            $query->where('mais_vendido', (bool) $filters['mais_vendido']);
        }

        // Busca por termo
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function($q) use ($search) {
                $q->where('descricao', 'LIKE', "%{$search}%")
                  ->orWhere('categoria', 'LIKE', "%{$search}%")
                  ->orWhere('referencia', 'LIKE', "%{$search}%")
                  ->orWhere('slug', 'LIKE', "%{$search}%");
            });
        }

        // Filtro por data
        if (!empty($filters['data_inicio']) && !empty($filters['data_fim'])) {
            $query->whereBetween('created_at', [$filters['data_inicio'], $filters['data_fim']]);
        }

        // Filtro por data_compra
        if (!empty($filters['data_compra_inicio']) && !empty($filters['data_compra_fim'])) {
            $query->whereBetween('data_compra', [$filters['data_compra_inicio'], $filters['data_compra_fim']]);
        }

        // Filtro por valor
        if (isset($filters['valor_min']) && isset($filters['valor_max'])) {
            $query->whereBetween('valor_atacado', [$filters['valor_min'], $filters['valor_max']]);
        }

        // Ordenação
        $ordenarPor = $filters['ordenar_por'] ?? 'created_at';
        $ordenarDir = $filters['ordenar_dir'] ?? 'desc';
        $query->orderBy($ordenarPor, $ordenarDir);

        return $query->paginate($perPage);
    }

    /**
     * Obter todas as categorias
     */
    public function getCategorias(): Collection
    {
        return Produto::select('categoria')
            ->distinct()
            ->whereNotNull('categoria')
            ->where('categoria', '!=', '')
            ->orderBy('categoria')
            ->pluck('categoria');
    }

    /**
     * Contar produtos por categoria
     */
    public function countByCategory(): Collection
    {
        return Produto::where('ativo', true)
            ->select('categoria', \DB::raw('count(*) as total'))
            ->groupBy('categoria')
            ->orderBy('total', 'desc')
            ->get();
    }

    /**
     * Buscar produtos com estoque baixo
     */
    public function getLowStock(int $limit = 5): Collection
    {
        return Produto::where('ativo', true)
            ->where('quantidade', '<=', 5)
            ->where('quantidade', '>', 0)
            ->orderBy('quantidade', 'asc')
            ->limit($limit)
            ->get();
    }

    /**
     * Buscar produtos sem estoque
     */
    public function getOutOfStock(): Collection
    {
        return Produto::where('ativo', true)
            ->where('quantidade', '<=', 0)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Buscar produtos em destaque
     */
    public function getDestaques(int $limit = 6): Collection
    {
        return Produto::where('ativo', true)
            ->where('destaque', true)
            ->where('quantidade', '>', 0)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Buscar produtos em promoção
     */
    public function getOfertas(int $limit = 6): Collection
    {
        return Produto::where('ativo', true)
            ->whereNotNull('preco_promocional')
            ->where('preco_promocional', '>', 0)
            ->where('quantidade', '>', 0)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Buscar produtos novos
     */
    public function getNovos(int $limit = 6): Collection
    {
        return Produto::where('ativo', true)
            ->where('novo', true)
            ->where('quantidade', '>', 0)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Buscar produtos mais vendidos
     */
    public function getMaisVendidos(int $limit = 6): Collection
    {
        return Produto::where('ativo', true)
            ->where('mais_vendido', true)
            ->where('quantidade', '>', 0)
            ->orderBy('visualizacoes', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Buscar produtos por categoria
     */
    public function getByCategoria(string $categoria, int $limit = null): Collection
    {
        $query = Produto::where('categoria', $categoria)
            ->where('ativo', true)
            ->where('quantidade', '>', 0);

        if ($limit) {
            $query->limit($limit);
        }

        return $query->orderBy('created_at', 'desc')->get();
    }

    /**
     * Buscar produtos relacionados
     */
    public function getRelacionados(int $produtoId, string $categoria, int $limit = 6): Collection
    {
        return Produto::where('categoria', $categoria)
            ->where('id', '!=', $produtoId)
            ->where('ativo', true)
            ->where('quantidade', '>', 0)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Buscar produtos por termo (search)
     */
    public function search(string $termo, int $limit = 15): Collection
    {
        return Produto::where('ativo', true)
            ->where('quantidade', '>', 0)
            ->where(function ($query) use ($termo) {
                $query->where('descricao', 'LIKE', "%{$termo}%")
                    ->orWhere('categoria', 'LIKE', "%{$termo}%")
                    ->orWhere('referencia', 'LIKE', "%{$termo}%")
                    ->orWhere('slug', 'LIKE', "%{$termo}%");
            })
            ->orderBy('descricao', 'asc')
            ->limit($limit)
            ->get();
    }

    /**
     * Contar produtos ativos
     */
    public function countAtivos(): int
    {
        return Produto::where('ativo', true)->count();
    }

    /**
     * Contar produtos inativos
     */
    public function countInativos(): int
    {
        return Produto::where('ativo', false)->count();
    }

    /**
     * Contar produtos com estoque
     */
    public function countComEstoque(): int
    {
        return Produto::where('quantidade', '>', 0)->count();
    }

    /**
     * Contar produtos sem estoque
     */
    public function countSemEstoque(): int
    {
        return Produto::where('quantidade', '<=', 0)->count();
    }

    /**
     * Atualizar em massa
     */
    public function bulkUpdate(array $ids, array $data): int
    {
        return Produto::whereIn('id', $ids)->update($data);
    }

    /**
     * Verificar se produto existe
     */
    public function exists(int $id): bool
    {
        return Produto::where('id', $id)->exists();
    }

    /**
     * Verificar se slug existe
     */
    public function slugExists(string $slug, ?int $excludeId = null): bool
    {
        $query = Produto::where('slug', $slug);
        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }
        return $query->exists();
    }

    /**
     * Verificar se referência existe
     */
    public function referenciaExists(string $referencia, ?int $excludeId = null): bool
    {
        $query = Produto::where('referencia', $referencia);
        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }
        return $query->exists();
    }
}