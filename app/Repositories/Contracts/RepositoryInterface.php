<?php
// app/Repositories/Contracts/RepositoryInterface.php

namespace App\Repositories\Contracts;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

interface RepositoryInterface
{
    /**
     * Obter todos os registros
     *
     * @param array $columns
     * @return Collection
     */
    public function all(array $columns = ['*']): Collection;

    /**
     * Paginar resultados
     *
     * @param int $perPage
     * @param array $columns
     * @return LengthAwarePaginator
     */
    public function paginate(int $perPage = 15, array $columns = ['*']): LengthAwarePaginator;

    /**
     * Buscar por ID
     *
     * @param int $id
     * @param array $columns
     * @return Model|null
     */
    public function find(int $id, array $columns = ['*']): ?Model;

    /**
     * Buscar por ID ou falhar
     *
     * @param int $id
     * @param array $columns
     * @return Model
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function findOrFail(int $id, array $columns = ['*']): Model;

    /**
     * Buscar por campo específico
     *
     * @param string $field
     * @param mixed $value
     * @param array $columns
     * @return Collection
     */
    public function findBy(string $field, $value, array $columns = ['*']): Collection;

    /**
     * Buscar primeiro registro por campo
     *
     * @param string $field
     * @param mixed $value
     * @param array $columns
     * @return Model|null
     */
    public function findFirstBy(string $field, $value, array $columns = ['*']): ?Model;

    /**
     * Criar um novo registro
     *
     * @param array $data
     * @return Model
     */
    public function create(array $data): Model;

    /**
     * Atualizar um registro
     *
     * @param int $id
     * @param array $data
     * @return Model
     */
    public function update(int $id, array $data): Model;

    /**
     * Atualizar ou criar
     *
     * @param array $attributes
     * @param array $values
     * @return Model
     */
    public function updateOrCreate(array $attributes, array $values = []): Model;

    /**
     * Deletar um registro
     *
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool;

    /**
     * Restaurar um registro soft-deleted
     *
     * @param int $id
     * @return bool
     */
    public function restore(int $id): bool;

    /**
     * Deletar permanentemente
     *
     * @param int $id
     * @return bool
     */
    public function forceDelete(int $id): bool;

    /**
     * Contar registros
     *
     * @param array $filters
     * @return int
     */
    public function count(array $filters = []): int;

    /**
     * Verificar se existe
     *
     * @param array $conditions
     * @return bool
     */
    public function exists(array $conditions): bool;

    /**
     * Buscar com relações
     *
     * @param array $relations
     * @return $this
     */
    public function with(array $relations): self;

    /**
     * Buscar com relações e condição where
     *
     * @param string $relation
     * @param callable $callback
     * @return $this
     */
    public function whereHas(string $relation, callable $callback): self;

    /**
     * Aplicar filtros
     *
     * @param array $filters
     * @return $this
     */
    public function filter(array $filters): self;

    /**
     * Ordenar resultados
     *
     * @param string $field
     * @param string $direction
     * @return $this
     */
    public function orderBy(string $field, string $direction = 'asc'): self;
}