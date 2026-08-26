<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected function setUp(): void
    {
        parent::setUp();

        // ✅ Reset cached roles and permissions
        app()->make(PermissionRegistrar::class)->forgetCachedPermissions();

        // ✅ Criar as roles para os testes
        try {
            // Garantir que a role Cliente existe
            Role::firstOrCreate([
                'name' => 'Cliente',
                'guard_name' => 'web'
            ]);

            // Garantir que a role Admin existe
            Role::firstOrCreate([
                'name' => 'Admin',
                'guard_name' => 'web'
            ]);

            // Garantir que a role Funcionario existe
            Role::firstOrCreate([
                'name' => 'Funcionario',
                'guard_name' => 'web'
            ]);
        } catch (\Exception $e) {
            // Se a tabela não existe, criar via migração
            $this->createPermissionTables();
        }

        // ✅ Limpar cache novamente
        app()->make(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    /**
     * Criar tabelas de permissão se não existirem
     */
    protected function createPermissionTables(): void
    {
        \Illuminate\Support\Facades\Artisan::call('migrate', [
            '--path' => 'vendor/spatie/laravel-permission/database/migrations',
            '--force' => true,
        ]);
    }
}