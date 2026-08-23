<?php

namespace App\Repositories\Contracts;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

interface RepositoryInterface
{
    public function all(array $columns = ['*']): Collection;
    public function paginate(int $perPage = 15, array $columns = ['*']): LengthAwarePaginator;
    public function find(int $id, array $columns = ['*']): ?Model;
    public function findOrFail(int $id, array $columns = ['*']): Model;
    public function findBy(string $field, mixed $value, array $columns = ['*']): Collection;
    public function findFirstBy(string $field, mixed $value, array $columns = ['*']): ?Model;
    public function create(array $data): Model;
    public function update(int $id, array $data): Model;
    public function updateOrCreate(array $attributes, array $values = []): Model;
    public function delete(int $id): bool;
    public function restore(int $id): bool;
    public function forceDelete(int $id): bool;
    public function count(array $filters = []): int;
    public function exists(array $conditions): bool;
    
    // Fluent methods
    public function with(array $relations): self;
    public function whereHas(string $relation, callable $callback): self;
    public function orderBy(string $field, string $direction = 'asc'): self;
}