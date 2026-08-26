<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // ============================================
        // 1. PERMISSÕES
        // ============================================
        
        $permissions = [
            'ver_produtos',
            'criar_produtos',
            'editar_produtos',
            'excluir_produtos',
            'ver_clientes',
            'criar_clientes',
            'editar_clientes',
            'excluir_clientes',
            'ver_pedidos',
            'criar_pedidos',
            'editar_pedidos',
            'cancelar_pedidos',
            'ver_relatorios',
            'gerenciar_usuarios',
            'gerenciar_permissoes'
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web'
            ]);
        }

        // ============================================
        // 2. PAPÉIS
        // ============================================

        // ADMIN - Acesso total
        $adminRole = Role::firstOrCreate(['name' => 'Admin', 'guard_name' => 'web']);
        $adminRole->syncPermissions($permissions);

        // FUNCIONÁRIO
        $funcionarioRole = Role::firstOrCreate(['name' => 'Funcionario', 'guard_name' => 'web']);
        $funcionarioRole->syncPermissions([
            'ver_produtos',
            'criar_produtos',
            'editar_produtos',
            'ver_clientes',
            'criar_clientes',
            'editar_clientes',
            'ver_pedidos',
            'criar_pedidos',
            'ver_relatorios'
        ]);

        // CLIENTE
        $clienteRole = Role::firstOrCreate(['name' => 'Cliente', 'guard_name' => 'web']);
        $clienteRole->syncPermissions([
            'ver_produtos',
            'ver_pedidos',
            'criar_pedidos'
        ]);

        // ============================================
        // 3. USUÁRIOS DE TESTE (SEM CPF)
        // ============================================

        // ✅ Admin - sem CPF
        $adminUser = User::firstOrCreate(
            ['email' => 'admin@loja.com'],
            [
                'name' => 'Administrador',
                'password' => Hash::make('12345678'),
                'telefone' => '(11) 99999-9999',
                'ativo' => true
            ]
        );
        $adminUser->assignRole('Admin');

        // ✅ Funcionário - sem CPF
        $funcUser = User::firstOrCreate(
            ['email' => 'funcionario@loja.com'],
            [
                'name' => 'Funcionário',
                'password' => Hash::make('12345678'),
                'telefone' => '(11) 88888-8888',
                'ativo' => true
            ]
        );
        $funcUser->assignRole('Funcionario');

        // ✅ Cliente - sem CPF
        $clienteUser = User::firstOrCreate(
            ['email' => 'cliente@loja.com'],
            [
                'name' => 'Cliente',
                'password' => Hash::make('12345678'),
                'telefone' => '(11) 77777-7777',
                'ativo' => true
            ]
        );
        $clienteUser->assignRole('Cliente');

        $this->command->info('✅ Permissões, papéis e usuários criados!');
        $this->command->info('📧 admin@loja.com / 12345678');
        $this->command->info('📧 funcionario@loja.com / 12345678');
        $this->command->info('📧 cliente@loja.com / 12345678');
    }
}