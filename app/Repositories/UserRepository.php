<?php
// app/Repositories/UserRepository.php

namespace App\Repositories;

use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class UserRepository extends BaseRepository implements UserRepositoryInterface
{
    /**
     * {@inheritdoc}
     */
    protected function model(): string
    {
        return User::class;
    }

    /**
     * {@inheritdoc}
     */
    public function findByEmail(string $email): ?User
    {
        $this->newQuery();
        return $this->query->where('email', $email)->first();
    }

    /**
     * {@inheritdoc}
     */
    public function findByRole(string $role): Collection
    {
        $this->newQuery();
        return $this->query->whereHas('roles', function($q) use ($role) {
            $q->where('name', $role);
        })->get();
    }

    /**
     * {@inheritdoc}
     */
    public function getActive(): Collection
    {
        $this->newQuery();
        return $this->query->where('active', true)->get();
    }

    /**
     * {@inheritdoc}
     */
    public function getInactive(): Collection
    {
        $this->newQuery();
        return $this->query->where('active', false)->get();
    }

    /**
     * {@inheritdoc}
     */
    public function getWithOrders(int $minOrders = 1): Collection
    {
        $this->newQuery();
        return $this->query->has('pedidos', '>=', $minOrders)
            ->withCount('pedidos')
            ->orderBy('pedidos_count', 'desc')
            ->get();
    }

    /**
     * {@inheritdoc}
     */
    public function toggleStatus(int $id, bool $active): User
    {
        $user = $this->findOrFail($id);
        $user->active = $active;
        $user->save();

        Log::info('Status do usuário alterado', [
            'user_id' => $id,
            'active' => $active
        ]);

        return $user->fresh();
    }

    /**
     * {@inheritdoc}
     */
    public function assignRole(int $userId, string $role): bool
    {
        try {
            $user = $this->findOrFail($userId);
            $user->assignRole($role);

            Log::info('Role atribuída ao usuário', [
                'user_id' => $userId,
                'role' => $role
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Erro ao atribuir role', [
                'user_id' => $userId,
                'role' => $role,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function removeRole(int $userId, string $role): bool
    {
        try {
            $user = $this->findOrFail($userId);
            $user->removeRole($role);

            Log::info('Role removida do usuário', [
                'user_id' => $userId,
                'role' => $role
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Erro ao remover role', [
                'user_id' => $userId,
                'role' => $role,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function hasPermission(int $userId, string $permission): bool
    {
        $user = $this->find($userId);
        if (!$user) {
            return false;
        }

        return $user->hasPermissionTo($permission);
    }

    /**
     * {@inheritdoc}
     */
    public function getStats(): array
    {
        return [
            'total' => $this->count(),
            'ativos' => $this->count(['active' => true]),
            'inativos' => $this->count(['active' => false]),
            'admins' => $this->count(['role' => 'Admin']),
            'funcionarios' => $this->count(['role' => 'Funcionario']),
            'clientes' => $this->count(['role' => 'Cliente']),
            'novos_hoje' => $this->count(['created_at' => ['>=', today()]]),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getFiltered(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $this->newQuery();

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $this->query->where(function($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                    ->orWhere('email', 'LIKE', "%{$search}%")
                    ->orWhere('phone', 'LIKE', "%{$search}%");
            });
        }

        if (!empty($filters['role'])) {
            $this->query->whereHas('roles', function($q) use ($filters) {
                $q->where('name', $filters['role']);
            });
        }

        if (!empty($filters['status'])) {
            if ($filters['status'] === 'active') {
                $this->query->where('active', true);
            } elseif ($filters['status'] === 'inactive') {
                $this->query->where('active', false);
            }
        }

        if (!empty($filters['order_by'])) {
            $direction = $filters['order_direction'] ?? 'asc';
            $this->query->orderBy($filters['order_by'], $direction);
        } else {
            $this->query->orderBy('created_at', 'desc');
        }

        return $this->query->paginate($perPage);
    }

    /**
     * {@inheritdoc}
     */
    public function getRecent(int $limit = 10): Collection
    {
        $this->newQuery();
        return $this->query->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Sobrescrever create para hash da senha
     */
    public function create(array $data): User
    {
        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }

        return parent::create($data);
    }

    /**
     * Sobrescrever update para hash da senha
     */
    public function update(int $id, array $data): User
    {
        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }

        return parent::update($id, $data);
    }
}