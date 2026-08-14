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

        // Reset cached roles and permissions
        app()->make(PermissionRegistrar::class)->forgetCachedPermissions();

        // Garantir que a role Cliente existe
        Role::firstOrCreate(['name' => 'Cliente', 'guard_name' => 'web']);
        
        // Limpar cache novamente
        app()->make(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
