<?php
// app/Repositories/BaseRepository.php

namespace App\Repositories;

use App\Repositories\Contracts\RepositoryInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Log;

abstract class BaseRepository implements RepositoryInterface
{
    /**
     * @var Model
     */
    protected Model $model;

    /**
     * @var Builder
     */
    protected Builder $query;

    /**
     * @var array
     */
    protected array $with = [];

    /**
     * @var array
     */
    protected array $filters = [];

    /**
     * @var string
     */
    protected string $orderField = 'created_at';

    /**
     * @var string
     */
    protected string $orderDirection = 'desc';

    /**
     * Construtor
     */
    public function __construct()
    {
        $this->makeModel();
        $this->newQuery();
    }

    /**
     * Definir o modelo
     *
     * @return string
     */
    abstract protected function model(): string;

    /**
     * Instanciar o modelo
     */
    protected function makeModel(): void
    {
        $model = app($this->model());

        if (!$model instanceof Model) {
            throw new \RuntimeException("Class {$this->model()} must be an instance of Illuminate\\Database\\Eloquent\\Model");
        }

        $this->model = $model;
    }

    /**
     * Resetar a query
     */
    protected function newQuery(): void
    {
        $this->query = $this->model->newQuery();

        // Aplicar relações
        if (!empty($this->with)) {
            $this->query->with($this->with);
        }

        // Aplicar filtros
        if (!empty($this->filters)) {
            $this->applyFilters();
        }

        // Aplicar ordenação
        $this->query->orderBy($this->orderField, $this->orderDirection);
    }

    /**
     * Aplicar filtros
     */
    protected function applyFilters(): void
    {
        foreach ($this->filters as $field => $value) {
            if ($value === null || $value === '') {
                continue;
            }

            if (is_array($value)) {
                // Filtro entre valores (range)
                if (isset($value['from']) && isset($value['to'])) {
                    $this->query->whereBetween($field, [$value['from'], $value['to']]);
                } elseif (isset($value['in'])) {
                    $this->query->whereIn($field, $value['in']);
                }
            } else {
                $this->query->where($field, $value);
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function all(array $columns = ['*']): Collection
    {
        $this->newQuery();
        return $this->query->get($columns);
    }

    /**
     * {@inheritdoc}
     */
    public function paginate(int $perPage = 15, array $columns = ['*']): LengthAwarePaginator
    {
        $this->newQuery();
        return $this->query->paginate($perPage, $columns);
    }

    /**
     * {@inheritdoc}
     */
    public function find(int $id, array $columns = ['*']): ?Model
    {
        $this->newQuery();
        return $this->query->find($id, $columns);
    }

    /**
     * {@inheritdoc}
     */
    public function findOrFail(int $id, array $columns = ['*']): Model
    {
        $this->newQuery();
        return $this->query->findOrFail($id, $columns);
    }

    /**
     * {@inheritdoc}
     */
    public function findBy(string $field, $value, array $columns = ['*']): Collection
    {
        $this->newQuery();
        return $this->query->where($field, $value)->get($columns);
    }

    /**
     * {@inheritdoc}
     */
    public function findFirstBy(string $field, $value, array $columns = ['*']): ?Model
    {
        $this->newQuery();
        return $this->query->where($field, $value)->first($columns);
    }

    /**
     * {@inheritdoc}
     */
    public function create(array $data): Model
    {
        try {
            $model = $this->model->create($data);
            Log::info('Registro criado', [
                'model' => class_basename($this->model),
                'id' => $model->id
            ]);
            return $model;
        } catch (\Exception $e) {
            Log::error('Erro ao criar registro', [
                'model' => class_basename($this->model),
                'data' => $data,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function update(int $id, array $data): Model
    {
        try {
            $model = $this->findOrFail($id);
            $model->update($data);
            Log::info('Registro atualizado', [
                'model' => class_basename($this->model),
                'id' => $id
            ]);
            return $model->fresh();
        } catch (\Exception $e) {
            Log::error('Erro ao atualizar registro', [
                'model' => class_basename($this->model),
                'id' => $id,
                'data' => $data,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function updateOrCreate(array $attributes, array $values = []): Model
    {
        return $this->model->updateOrCreate($attributes, $values);
    }

    /**
     * {@inheritdoc}
     */
    public function delete(int $id): bool
    {
        try {
            $model = $this->findOrFail($id);
            $deleted = $model->delete();
            Log::info('Registro deletado', [
                'model' => class_basename($this->model),
                'id' => $id
            ]);
            return $deleted;
        } catch (\Exception $e) {
            Log::error('Erro ao deletar registro', [
                'model' => class_basename($this->model),
                'id' => $id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function restore(int $id): bool
    {
        try {
            $model = $this->model->withTrashed()->findOrFail($id);
            $restored = $model->restore();
            Log::info('Registro restaurado', [
                'model' => class_basename($this->model),
                'id' => $id
            ]);
            return $restored;
        } catch (\Exception $e) {
            Log::error('Erro ao restaurar registro', [
                'model' => class_basename($this->model),
                'id' => $id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function forceDelete(int $id): bool
    {
        try {
            $model = $this->model->withTrashed()->findOrFail($id);
            $deleted = $model->forceDelete();
            Log::info('Registro deletado permanentemente', [
                'model' => class_basename($this->model),
                'id' => $id
            ]);
            return $deleted;
        } catch (\Exception $e) {
            Log::error('Erro ao deletar permanentemente', [
                'model' => class_basename($this->model),
                'id' => $id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function count(array $filters = []): int
    {
        $this->newQuery();
        foreach ($filters as $field => $value) {
            $this->query->where($field, $value);
        }
        return $this->query->count();
    }

    /**
     * {@inheritdoc}
     */
    public function exists(array $conditions): bool
    {
        $this->newQuery();
        foreach ($conditions as $field => $value) {
            $this->query->where($field, $value);
        }
        return $this->query->exists();
    }

    /**
     * {@inheritdoc}
     */
    public function with(array $relations): self
    {
        $this->with = array_merge($this->with, $relations);
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function whereHas(string $relation, callable $callback): self
    {
        $this->query->whereHas($relation, $callback);
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function filter(array $filters): self
    {
        $this->filters = array_merge($this->filters, $filters);
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function orderBy(string $field, string $direction = 'asc'): self
    {
        $this->orderField = $field;
        $this->orderDirection = $direction;
        return $this;
    }

    /**
     * Iniciar uma transação
     */
    public function beginTransaction(): void
    {
        \DB::beginTransaction();
    }

    /**
     * Commit da transação
     */
    public function commitTransaction(): void
    {
        \DB::commit();
    }

    /**
     * Rollback da transação
     */
    public function rollbackTransaction(): void
    {
        \DB::rollBack();
    }

    /**
     * Executar em transação
     *
     * @param callable $callback
     * @return mixed
     * @throws \Exception
     */
    public function transaction(callable $callback)
    {
        return \DB::transaction($callback);
    }
}