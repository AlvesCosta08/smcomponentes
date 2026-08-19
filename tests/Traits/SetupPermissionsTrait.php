<?php
// tests/Traits/SetupPermissionsTrait.php

namespace Tests\Traits;

use Illuminate\Support\Facades\Artisan;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

trait SetupPermissionsTrait
{
    /**
     * Configurar permissões e roles para testes
     */
    protected function setUpPermissions(): void
    {
        // Verificar se a tabela de permissões existe
        if (!app()->make(\Spatie\Permission\PermissionRegistrar::class)->getPermissions()) {
            // Executar migrations de permissões se não existirem
            Artisan::call('migrate', [
                '--path' => 'vendor/spatie/laravel-permission/database/migrations',
                '--force' => true,
            ]);
        }

        // Recarregar o registro de permissões
        app()->make(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();

        // Criar roles se não existirem
        $roles = ['Admin', 'Funcionario', 'Cliente'];
        foreach ($roles as $role) {
            if (!Role::where('name', $role)->exists()) {
                Role::create(['name' => $role, 'guard_name' => 'web']);
            }
        }

        // Criar permissões básicas se não existirem
        $permissions = [
            'view_dashboard',
            'manage_products',
            'manage_orders',
            'manage_users',
            'manage_banners',
        ];

        foreach ($permissions as $permission) {
            if (!Permission::where('name', $permission)->exists()) {
                Permission::create(['name' => $permission, 'guard_name' => 'web']);
            }
        }

        // Atribuir permissões aos roles
        $adminRole = Role::findByName('Admin', 'web');
        $adminRole->syncPermissions(Permission::all());
    }
}
