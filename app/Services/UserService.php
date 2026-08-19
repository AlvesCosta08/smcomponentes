<?php
// app/Services/UserService.php

namespace App\Services;

use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserService
{
    /**
     * Listar usuários com filtros
     */
    public function listUsers(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = User::query();

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('email', 'LIKE', "%{$search}%")
                  ->orWhere('cpf', 'LIKE', "%{$search}%");
            });
        }

        if (!empty($filters['role'])) {
            $query->role($filters['role']);
        }

        if (isset($filters['status']) && $filters['status'] !== '') {
            $query->where('ativo', $filters['status']);
        }

        if (!empty($filters['data_inicio'])) {
            $query->whereDate('created_at', '>=', $filters['data_inicio']);
        }

        if (!empty($filters['data_fim'])) {
            $query->whereDate('created_at', '<=', $filters['data_fim']);
        }

        return $query->with('roles')->orderBy('created_at', 'desc')->paginate($perPage);
    }

    /**
     * Criar novo usuário
     */
    public function createUser(array $data): User
    {
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'telefone' => $data['telefone'] ?? null,
            'celular' => $data['celular'] ?? null,
            'cpf' => $data['cpf'] ?? null,
            'data_nascimento' => $data['data_nascimento'] ?? null,
            'cep' => $data['cep'] ?? null,
            'logradouro' => $data['logradouro'] ?? null,
            'numero' => $data['numero'] ?? null,
            'complemento' => $data['complemento'] ?? null,
            'bairro' => $data['bairro'] ?? null,
            'cidade' => $data['cidade'] ?? null,
            'estado' => $data['estado'] ?? null,
            'ativo' => $data['ativo'] ?? true,
        ]);

        $user->assignRole($data['role']);

        return $user;
    }

    /**
     * Atualizar usuário
     */
    public function updateUser(User $user, array $data): User
    {
        $dados = [
            'name' => $data['name'],
            'email' => $data['email'],
            'telefone' => $data['telefone'] ?? null,
            'celular' => $data['celular'] ?? null,
            'cpf' => $data['cpf'] ?? null,
            'data_nascimento' => $data['data_nascimento'] ?? null,
            'cep' => $data['cep'] ?? null,
            'logradouro' => $data['logradouro'] ?? null,
            'numero' => $data['numero'] ?? null,
            'complemento' => $data['complemento'] ?? null,
            'bairro' => $data['bairro'] ?? null,
            'cidade' => $data['cidade'] ?? null,
            'estado' => $data['estado'] ?? null,
            'ativo' => $data['ativo'] ?? true,
        ];

        if (!empty($data['password'])) {
            $dados['password'] = Hash::make($data['password']);
        }

        $user->update($dados);
        $user->syncRoles([$data['role']]);

        return $user->fresh();
    }

    /**
     * Obter estatísticas de usuários
     */
    public function getStats(): array
    {
        return [
            'total' => User::count(),
            'clientes' => User::role('Cliente')->count(),
            'admins' => User::role('Admin')->count(),
            'funcionarios' => User::role('Funcionario')->count(),
            'ativos' => User::where('ativo', true)->count(),
            'inativos' => User::where('ativo', false)->count(),
        ];
    }

    /**
     * Obter todas as roles
     */
    public function getRoles(): array
    {
        return Role::all()->pluck('name')->toArray();
    }
}