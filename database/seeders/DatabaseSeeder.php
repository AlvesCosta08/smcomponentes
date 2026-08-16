<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,          // Roles e usuários admin/funcionario/cliente
            ProdutoSeeder::class, 
            AdminUserSeeder::class,      // Produtos (1668+ produtos)
            ClienteSeederFinal::class,  // 🆕 Clientes do CSV
        ]);
    }
}