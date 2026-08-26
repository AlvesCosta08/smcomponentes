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
            'manage_users',
            'view_users',
            'create_users',
            'edit_users',
            'delete_users',
            'manage_products',
            'view_products',
            'create_products',
            'edit_products',
            'delete_products',
            'manage_orders',
            'view_orders',
            'create_orders',
            'edit_orders',
            'delete_orders',
            'update_order_status',
            'manage_banners',
            'view_banners',
            'create_banners',
            'edit_banners',
            'delete_banners',
            'manage_categories',
            'view_categories',
            'create_categories',
            'edit_categories',
            'delete_categories',
            'view_reports',
            'view_sales_reports',
            'view_products_reports',
            'view_users_reports',
            'manage_settings',
            'manage_payment_settings',
            'manage_email_settings',
            'manage_mercadopago',
            'view_mercadopago_config',
            'edit_mercadopago_config',
            'manage_wishlist',
            'view_wishlist',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web'
            ]);
        }

        // ============================================
        // 2. CRIAR ROLES
        // ============================================
        $adminRole = Role::firstOrCreate([
            'name' => 'admin',
            'guard_name' => 'web'
        ]);
        $adminRole->syncPermissions($permissions);

        $managerRole = Role::firstOrCreate([
            'name' => 'manager',
            'guard_name' => 'web'
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
            'name' => 'user',
            'guard_name' => 'web'
        ]);

        // ============================================
        // 3. CRIAR USUÁRIO ADMIN (COM CNPJ VÁLIDO)
        // ============================================
        $admin = User::firstOrCreate(
            ['email' => 'admin@smcomponentes.com'],
            [
                'name' => 'Administrador Sistema',
                'email' => 'admin@smcomponentes.com',
                'password' => Hash::make('admin123'),
                'email_verified_at' => Carbon::now(),
                
                'telefone' => '(11) 99999-9999',
                'celular' => '(11) 98888-8888',
                // ✅ CORRIGIDO: Apenas números, sem formatação
                'cnpj' => '12345678000190',
                'ie' => '123456789',
                'data_nascimento' => '1980-01-01',
                
                'cep' => '01234-567',
                'logradouro' => 'Rua do Administrador',
                'numero' => '100',
                'complemento' => 'Sala 01',
                'bairro' => 'Centro',
                'cidade' => 'São Paulo',
                'estado' => 'SP',
                
                'ativo' => true,
                'ultimo_acesso' => Carbon::now(),
                'deleted_at' => null,
            ]
        );
        $admin->assignRole('admin');

        // ============================================
        // 4. CRIAR USUÁRIO MANAGER (CNPJ NULO)
        // ============================================
        $manager = User::firstOrCreate(
            ['email' => 'gerente@smcomponentes.com'],
            [
                'name' => 'Gerente Geral',
                'email' => 'gerente@smcomponentes.com',
                'password' => Hash::make('gerente123'),
                'email_verified_at' => Carbon::now(),
                
                'telefone' => '(11) 77777-7777',
                'celular' => '(11) 97777-7777',
                // ✅ Mantém null
                'cnpj' => null,
                'ie' => null,
                'data_nascimento' => '1985-05-15',
                
                'cep' => '98765-432',
                'logradouro' => 'Avenida do Gerente',
                'numero' => '200',
                'complemento' => 'Sala 02',
                'bairro' => 'Jardim',
                'cidade' => 'São Paulo',
                'estado' => 'SP',
                
                'ativo' => true,
                'ultimo_acesso' => Carbon::now(),
                'deleted_at' => null,
            ]
        );
        $manager->assignRole('manager');

        // ============================================
        // 5. CRIAR USUÁRIO CLIENTE (CNPJ NULO)
        // ============================================
        $user = User::firstOrCreate(
            ['email' => 'cliente@smcomponentes.com'],
            [
                'name' => 'Cliente Teste',
                'email' => 'cliente@smcomponentes.com',
                'password' => Hash::make('cliente123'),
                'email_verified_at' => Carbon::now(),
                
                'telefone' => '(11) 55555-5555',
                'celular' => '(11) 95555-5555',
                // ✅ Mantém null
                'cnpj' => null,
                'ie' => null,
                'data_nascimento' => '1995-10-20',
                
                'cep' => '54321-876',
                'logradouro' => 'Rua do Cliente',
                'numero' => '300',
                'complemento' => 'Apto 10',
                'bairro' => 'Vila',
                'cidade' => 'São Paulo',
                'estado' => 'SP',
                
                'ativo' => true,
                'ultimo_acesso' => Carbon::now(),
                'deleted_at' => null,
            ]
        );
        $user->assignRole('user');

        // ============================================
        // 6. EXIBIR INFORMAÇÕES
        // ============================================
        $this->command->info('╔═══════════════════════════════════════════════════════╗');
        $this->command->info('║     ✅ USUÁRIOS CRIADOS COM SUCESSO!                 ║');
        $this->command->info('╚═══════════════════════════════════════════════════════╝');
        $this->command->info('');
        
        $this->command->info('👑 ADMINISTRADOR:');
        $this->command->info('   📧 Email: admin@smcomponentes.com');
        $this->command->info('   🔑 Senha: admin123');
        $this->command->info('   👔 Role: admin');
        $this->command->info('   📋 CNPJ: 12.345.678/0001-90');
        $this->command->info('');
        
        $this->command->info('👔 GERENTE:');
        $this->command->info('   📧 Email: gerente@smcomponentes.com');
        $this->command->info('   🔑 Senha: gerente123');
        $this->command->info('   👔 Role: manager');
        $this->command->info('');
        
        $this->command->info('👤 CLIENTE:');
        $this->command->info('   📧 Email: cliente@smcomponentes.com');
        $this->command->info('   🔑 Senha: cliente123');
        $this->command->info('   👔 Role: user');
        $this->command->info('');
        
        $this->command->info('╔═══════════════════════════════════════════════════════╗');
        $this->command->info('║  💡 Acesse: http://localhost:8000/login             ║');
        $this->command->info('╚═══════════════════════════════════════════════════════╝');
    }
}