<?php
// app/Repositories/Contracts/UserRepositoryInterface.php

namespace App\Repositories\Contracts;

use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface UserRepositoryInterface extends RepositoryInterface
{
    /**
     * Buscar usuário por email
     *
     * @param string $email
     * @return User|null
     */
    public function findByEmail(string $email): ?User;

    /**
     * Buscar usuários por role
     *
     * @param string $role
     * @return Collection
     */
    public function findByRole(string $role): Collection;

    /**
     * Buscar usuários ativos
     *
     * @return Collection
     */
    public function getActive(): Collection;

    /**
     * Buscar usuários inativos
     *
     * @return Collection
     */
    public function getInactive(): Collection;

    /**
     * Buscar usuários com pedidos
     *
     * @param int $minOrders
     * @return Collection
     */
    public function getWithOrders(int $minOrders = 1): Collection;

    /**
     * Ativar/desativar usuário
     *
     * @param int $id
     * @param bool $active
     * @return User
     */
    public function toggleStatus(int $id, bool $active): User;

    /**
     * Atribuir role ao usuário
     *
     * @param int $userId
     * @param string $role
     * @return bool
     */
    public function assignRole(int $userId, string $role): bool;

    /**
     * Remover role do usuário
     *
     * @param int $userId
     * @param string $role
     * @return bool
     */
    public function removeRole(int $userId, string $role): bool;

    /**
     * Verificar se usuário tem permissão
     *
     * @param int $userId
     * @param string $permission
     * @return bool
     */
    public function hasPermission(int $userId, string $permission): bool;

    /**
     * Obter estatísticas de usuários
     *
     * @return array
     */
    public function getStats(): array;

    /**
     * Buscar usuários com filtros
     *
     * @param array $filters
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getFiltered(array $filters = [], int $perPage = 15): LengthAwarePaginator;

    /**
     * Obter últimos usuários cadastrados
     *
     * @param int $limit
     * @return Collection
     */
    public function getRecent(int $limit = 10): Collection;
}