<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('🚀 Iniciando seeders...');
        $this->command->info('');

        // 1. PERMISSÕES E PAPÉIS (primeiro)
        $this->command->info('📋 1. Criando permissões e papéis...');
        $this->call(RoleSeeder::class);
        $this->command->info('✅ Papéis criados!');
        $this->command->info('');

        // 2. ADMIN E USUÁRIOS (depois dos papéis)
        $this->command->info('👑 2. Criando usuários admin...');
        $this->call(AdminUserSeeder::class);
        $this->command->info('✅ Admin criado!');
        $this->command->info('');

        // 3. PRODUTOS (pode ser antes ou depois dos clientes)
        $this->command->info('📦 3. Importando produtos...');
        $this->call(ProdutoSeeder::class);
        $this->command->info('✅ Produtos importados!');
        $this->command->info('');

        // 4. CLIENTES (último, pois depende de papéis já criados)
        $this->command->info('👤 4. Importando clientes do CSV...');
        $this->call(ClienteSeederFinal::class);
        $this->command->info('✅ Clientes importados!');
        $this->command->info('');

        // 5. Resumo final
        $this->command->info('╔═══════════════════════════════════════════════════════╗');
        $this->command->info('║     ✅ TODOS OS SEEDERS EXECUTADOS COM SUCESSO!      ║');
        $this->command->info('╚═══════════════════════════════════════════════════════╝');
        $this->command->info('');

        // Mostrar estatísticas
        $this->command->info('📊 ESTATÍSTICAS:');
        
        try {
            $totalUsuarios = \App\Models\User::count();
            $this->command->info("   👥 Usuários: {$totalUsuarios}");
        } catch (\Exception $e) {
            $this->command->warn('   ⚠️  Não foi possível contar usuários');
        }
        
        try {
            $totalProdutos = \App\Models\Produto::count();
            $this->command->info("   📦 Produtos: {$totalProdutos}");
        } catch (\Exception $e) {
            $this->command->warn('   ⚠️  Não foi possível contar produtos');
        }
        
        try {
            $totalCategorias = \App\Models\Categoria::count();
            $this->command->info("   📂 Categorias: {$totalCategorias}");
        } catch (\Exception $e) {
            $this->command->warn('   ⚠️  Não foi possível contar categorias');
        }

        $this->command->info('');
        $this->command->info('🔑 CREDENCIAIS DE ACESSO:');
        $this->command->info('   👑 Admin: admin@smcomponentes.com / admin123');
        $this->command->info('   👔 Gerente: gerente@smcomponentes.com / gerente123');
        $this->command->info('   👤 Cliente: cliente@smcomponentes.com / cliente123');
        
        $this->command->info('');
        $this->command->info('🌐 Acesse: http://localhost:8000/login');
    }
}
