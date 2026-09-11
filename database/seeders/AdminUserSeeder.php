<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        // ============================================
        // 1. CRIAR PERMISSÕES
        // ============================================
        $permissions = [
            'view_dashboard',
            'manage_users', 'view_users', 'create_users', 'edit_users', 'delete_users',
            'manage_products', 'view_products', 'create_products', 'edit_products', 'delete_products',
            'manage_orders', 'view_orders', 'create_orders', 'edit_orders', 'delete_orders', 'update_order_status',
            'manage_banners', 'view_banners', 'create_banners', 'edit_banners', 'delete_banners',
            'manage_categories', 'view_categories', 'create_categories', 'edit_categories', 'delete_categories',
            'view_reports', 'view_sales_reports', 'view_products_reports', 'view_users_reports',
            'manage_settings', 'manage_payment_settings', 'manage_email_settings',
            'manage_mercadopago', 'view_mercadopago_config', 'edit_mercadopago_config',
            'manage_wishlist', 'view_wishlist',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web',
            ]);
        }

        // ============================================
        // 2. CRIAR ROLES
        // ============================================
        $adminRole = Role::firstOrCreate([
            'name' => 'Admin',
            'guard_name' => 'web',
        ]);
        $adminRole->syncPermissions($permissions);

        $managerRole = Role::firstOrCreate([
            'name' => 'Funcionario',
            'guard_name' => 'web',
        ]);
        $managerRole->syncPermissions([
            'view_dashboard',
            'view_users',
            'view_products',
            'view_orders',
            'view_banners',
            'view_categories',
            'view_reports',
        ]);

        Role::firstOrCreate([
            'name' => 'Cliente',
            'guard_name' => 'web',
        ]);

        // ============================================
        // 3. ADMIN — CNPJ VÁLIDO
        // ============================================
        $admin = User::firstOrCreate(
            ['email' => 'admin@smcomponentes.com'],
            [
                'name'              => 'Administrador Sistema',
                'email'             => 'admin@smcomponentes.com',
                'password'          => Hash::make('admin123'),
                'email_verified_at' => Carbon::now(),

                'telefone'          => '11999999999',
                'celular'           => '11988888888',
                'cnpj'              => '11.222.333/0001-81',   // ✅ CNPJ VÁLIDO de teste
                'ie'                => '123456789',
                'data_nascimento'   => '1980-01-01',

                'cep'               => '01234567',
                'logradouro'        => 'Rua do Administrador',
                'numero'            => '100',
                'complemento'       => 'Sala 01',
                'bairro'            => 'Centro',
                'cidade'            => 'São Paulo',
                'estado'            => 'SP',

                'ativo'             => true,
                'ultimo_acesso'     => Carbon::now(),
            ]
        );
        $admin->assignRole('Admin');

        // ============================================
        // 4. FUNCIONÁRIO — CNPJ VÁLIDO
        // ============================================
        $manager = User::firstOrCreate(
            ['email' => 'funcionario@smcomponentes.com'],
            [
                'name'              => 'Funcionário Geral',
                'email'             => 'funcionario@smcomponentes.com',
                'password'          => Hash::make('func123'),
                'email_verified_at' => Carbon::now(),

                'telefone'          => '11777777777',
                'celular'           => '11977777777',
                'cnpj'              => '11.444.777/0001-61',   // ✅ CNPJ VÁLIDO de teste
                'ie'                => null,
                'data_nascimento'   => '1985-05-15',

                'cep'               => '98765432',
                'logradouro'        => 'Avenida do Funcionário',
                'numero'            => '200',
                'complemento'       => 'Sala 02',
                'bairro'            => 'Jardim',
                'cidade'            => 'São Paulo',
                'estado'            => 'SP',

                'ativo'             => true,
                'ultimo_acesso'     => Carbon::now(),
            ]
        );
        $manager->assignRole('Funcionario');

        // ============================================
        // 5. CLIENTE TESTE — CNPJ VÁLIDO
        // ============================================
        $user = User::firstOrCreate(
            ['email' => 'cliente@smcomponentes.com'],
            [
                'name'              => 'Cliente Teste',
                'email'             => 'cliente@smcomponentes.com',
                'password'          => Hash::make('cliente123'),
                'email_verified_at' => Carbon::now(),

                'telefone'          => '11555555555',
                'celular'           => '11955555555',
                'cnpj'              => '45.997.418/0001-53',   // ✅ CNPJ VÁLIDO de teste
                'ie'                => null,
                'data_nascimento'   => '1995-10-20',

                'cep'               => '54321876',
                'logradouro'        => 'Rua do Cliente',
                'numero'            => '300',
                'complemento'       => 'Apto 10',
                'bairro'            => 'Vila',
                'cidade'            => 'São Paulo',
                'estado'            => 'SP',

                'ativo'             => true,
                'ultimo_acesso'     => Carbon::now(),
            ]
        );
        $user->assignRole('Cliente');

        // ============================================
        // 6. LOG
        // ============================================
        $this->command->info('╔═══════════════════════════════════════════════════════╗');
        $this->command->info('║     ✅ USUÁRIOS CRIADOS COM SUCESSO!                 ║');
        $this->command->info('╚═══════════════════════════════════════════════════════╝');
        $this->command->info('');
        $this->command->info('👑 ADMIN:       admin@smcomponentes.com / admin123');
        $this->command->info('👔 FUNCIONÁRIO: funcionario@smcomponentes.com / func123');
        $this->command->info('👤 CLIENTE:     cliente@smcomponentes.com / cliente123');
        $this->command->info('');
    }
}