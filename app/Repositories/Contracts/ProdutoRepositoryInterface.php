<?php
// app/Repositories/Contracts/ProdutoRepositoryInterface.php

namespace App\Repositories\Contracts;

use App\Models\Produto;
use Illuminate\Database\Eloquent\Collection;

interface ProdutoRepositoryInterface extends RepositoryInterface
{
    /**
     * Buscar produtos por categoria
     *
     * @param string $categoria
     * @param int $limit
     * @return Collection
     */
    public function findByCategoria(string $categoria, int $limit = null): Collection;

    /**
     * Buscar produtos em destaque
     *
     * @param int $limit
     * @return Collection
     */
    public function getDestaques(int $limit = 8): Collection;

    /**
     * Buscar produtos em oferta
     *
     * @param int $limit
     * @return Collection
     */
    public function getOfertas(int $limit = 8): Collection;

    /**
     * Buscar produtos recentes
     *
     * @param int $limit
     * @return Collection
     */
    public function getRecentes(int $limit = 8): Collection;

    /**
     * Buscar produtos por slug
     *
     * @param string $slug
     * @return Produto|null
     */
    public function findBySlug(string $slug): ?Produto;

    /**
     * Buscar produtos com estoque baixo
     *
     * @param int $threshold
     * @return Collection
     */
    public function getLowStock(int $threshold = 5): Collection;

    /**
     * Buscar produtos sem estoque
     *
     * @return Collection
     */
    public function getOutOfStock(): Collection;

    /**
     * Buscar produtos com paginação e filtros
     *
     * @param array $filters
     * @param int $perPage
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function getFiltered(array $filters = [], int $perPage = 20): \Illuminate\Pagination\LengthAwarePaginator;

    /**
     * Incrementar visualizações
     *
     * @param int $id
     * @return bool
     */
    public function incrementViews(int $id): bool;

    /**
     * Buscar produtos relacionados
     *
     * @param int $productId
     * @param string $categoria
     * @param int $limit
     * @return Collection
     */
    public function getRelated(int $productId, string $categoria, int $limit = 4): Collection;

    /**
     * Buscar produtos por faixa de preço
     *
     * @param float $min
     * @param float $max
     * @return Collection
     */
    public function findByPriceRange(float $min, float $max): Collection;

    /**
     * Buscar produtos disponíveis
     *
     * @param array $columns
     * @return Collection
     */
    public function getAvailable(array $columns = ['*']): Collection;

    /**
     * Buscar produtos por busca textual
     *
     * @param string $term
     * @param int $limit
     * @return Collection
     */
    public function search(string $term, int $limit = 10): Collection;

    /**
     * Atualizar status de disponibilidade baseado no estoque
     *
     * @param int $id
     * @return bool
     */
    public function updateAvailability(int $id): bool;

    /**
     * Obter estatísticas de produtos
     *
     * @return array
     */
    public function getStats(): array;

    /**
     * Importar produtos em massa
     *
     * @param array $products
     * @return array ['imported' => int, 'failed' => int]
     */
    public function importBatch(array $products): array;
}