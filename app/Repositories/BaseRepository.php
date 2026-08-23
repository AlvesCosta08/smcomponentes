<?php

namespace App\Repositories;

use App\Repositories\Contracts\RepositoryInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use RuntimeException;

abstract class BaseRepository implements RepositoryInterface
{
    protected Model $model;

    public function __construct()
    {
        $this->makeModel();
    }

    abstract protected function model(): string;

    protected function makeModel(): void
    {
        $model = app($this->model());
        if (!$model instanceof Model) {
            throw new RuntimeException("Class {$this->model()} must be an instance of Illuminate\\Database\\Eloquent\\Model");
        }
        $this->model = $model;
    }

    public function all(array $columns = ['*']): Collection
    {
        return $this->model->newQuery()->get($columns);
    }

    public function paginate(int $perPage = 15, array $columns = ['*']): LengthAwarePaginator
    {
        return $this->model->newQuery()->paginate($perPage, $columns);
    }

    public function find(int $id, array $columns = ['*']): ?Model
    {
        return $this->model->newQuery()->find($id, $columns);
    }

    public function findOrFail(int $id, array $columns = ['*']): Model
    {
        return $this->model->newQuery()->findOrFail($id, $columns);
    }

    public function findBy(string $field, mixed $value, array $columns = ['*']): Collection
    {
        return $this->model->newQuery()->where($field, $value)->get($columns);
    }

    public function findFirstBy(string $field, mixed $value, array $columns = ['*']): ?Model
    {
        return $this->model->newQuery()->where($field, $value)->first($columns);
    }

    public function create(array $data): Model
    {
        try {
            $model = $this->model->create($data);
            Log::info('Registro criado', ['model' => class_basename($this->model), 'id' => $model->id]);
            return $model;
        } catch (\Exception $e) {
            Log::error('Erro ao criar registro', ['model' => class_basename($this->model), 'error' => $e->getMessage()]);
            throw $e;
        }
    }

    public function update(int $id, array $data): Model
    {
        try {
            $model = $this->findOrFail($id);
            $model->update($data);
            Log::info('Registro atualizado', ['model' => class_basename($this->model), 'id' => $id]);
            return $model->fresh();
        } catch (\Exception $e) {
            Log::error('Erro ao atualizar registro', ['model' => class_basename($this->model), 'id' => $id, 'error' => $e->getMessage()]);
            throw $e;
        }
    }

    public function updateOrCreate(array $attributes, array $values = []): Model
    {
        return $this->model->updateOrCreate($attributes, $values);
    }

    public function delete(int $id): bool
    {
        return (bool) $this->findOrFail($id)->delete();
    }

    public function restore(int $id): bool
    {
        return (bool) $this->model->withTrashed()->findOrFail($id)->restore();
    }

    public function forceDelete(int $id): bool
    {
        return (bool) $this->model->withTrashed()->findOrFail($id)->forceDelete();
    }

    public function count(array $filters = []): int
    {
        $query = $this->model->newQuery();
        foreach ($filters as $field => $value) {
            $query->where($field, $value);
        }
        return $query->count();
    }

    public function exists(array $conditions): bool
    {
        $query = $this->model->newQuery();
        foreach ($conditions as $field => $value) {
            $query->where($field, $value);
        }
        return $query->exists();
    }

    public function with(array $relations): self
    {
        // Nota: Em um base repository imutável, isso exigiria clonar o objeto. 
        // Para simplicidade no Laravel, aplicamos direto na próxima chamada ou usamos scopes.
        return $this;
    }

    public function whereHas(string $relation, callable $callback): self
    {
        return $this;
    }

    public function orderBy(string $field, string $direction = 'asc'): self
    {
        return $this;
    }

    public function beginTransaction(): void { DB::beginTransaction(); }
    public function commitTransaction(): void { DB::commit(); }
    public function rollbackTransaction(): void { DB::rollBack(); }
    
    public function transaction(callable $callback): mixed
    {
        return DB::transaction($callback);
    }
}