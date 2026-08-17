<?php
// app/Services/Contracts/UserServiceInterface.php

namespace App\Services\Contracts;

use App\Models\User;
use App\DTOs\UserDTO;
use Illuminate\Pagination\LengthAwarePaginator;

interface UserServiceInterface
{
    /**
     * Listar usuários com filtros
     *
     * @param array $filters ['role' => string, 'search' => string, 'status' => string]
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function listUsers(array $filters = [], int $perPage = 15): LengthAwarePaginator;

    /**
     * Criar um novo usuário
     *
     * @param UserDTO $dto
     * @return User
     */
    public function createUser(UserDTO $dto): User;

    /**
     * Atualizar um usuário
     *
     * @param int $id
     * @param UserDTO $dto
     * @return User
     */
    public function updateUser(int $id, UserDTO $dto): User;

    /**
     * Desativar um usuário
     *
     * @param int $id
     * @return bool
     */
    public function deactivateUser(int $id): bool;

    /**
     * Ativar um usuário
     *
     * @param int $id
     * @return bool
     */
    public function activateUser(int $id): bool;

    /**
     * Excluir um usuário (soft delete)
     *
     * @param int $id
     * @return bool
     */
    public function deleteUser(int $id): bool;

    /**
     * Restaurar um usuário excluído
     *
     * @param int $id
     * @return bool
     */
    public function restoreUser(int $id): bool;

    /**
     * Atribuir role a um usuário
     *
     * @param int $userId
     * @param string $role Admin|Funcionario|Cliente
     * @return bool
     */
    public function assignRole(int $userId, string $role): bool;

    /**
     * Remover role de um usuário
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
     * Obter histórico de pedidos do usuário
     *
     * @param int $userId
     * @param int $limit
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getUserOrderHistory(int $userId, int $limit = 10): \Illuminate\Database\Eloquent\Collection;

    /**
     * Atualizar perfil do usuário
     *
     * @param int $id
     * @param array $data
     * @return User
     */
    public function updateProfile(int $id, array $data): User;

    /**
     * Alterar senha do usuário
     *
     * @param int $id
     * @param string $currentPassword
     * @param string $newPassword
     * @return bool
     */
    public function changePassword(int $id, string $currentPassword, string $newPassword): bool;
}