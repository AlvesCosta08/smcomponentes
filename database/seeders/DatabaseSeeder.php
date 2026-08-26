<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            BannerSeeder::class,
            ProdutoSeederFixed::class,
            // ✅ REMOVER AdminUserSeeder - RoleSeeder já cria os usuários
        ]);
    }
}