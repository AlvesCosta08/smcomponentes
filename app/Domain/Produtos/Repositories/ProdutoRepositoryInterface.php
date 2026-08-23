<?php

namespace App\Domain\Produtos\Repositories;

use App\Models\Produto;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface ProdutoRepositoryInterface
{
    // Operações Básicas (CRUD)
    public function create(array $data): Produto;
    public function find(int $id): ?Produto;
    public function findOrFail(int $id): Produto;
    public function update(Produto $produto, array $data): bool;
    public function delete(Produto $produto): bool;
    
    // Consultas Específicas do Domínio
    public function findBySlug(string $slug): ?Produto;
    public function getFiltered(array $filters, int $perPage): LengthAwarePaginator;
    public function getDestaques(int $limit): Collection;
    public function getOfertas(int $limit): Collection;
    public function getLowStock(int $threshold): Collection;
    public function getOutOfStock(): Collection;
    public function incrementViews(int $id): bool;
}