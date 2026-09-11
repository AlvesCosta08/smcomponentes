<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Executa a migration: ADICIONA a coluna valor_atacado
     */
    public function up(): void
    {
        Schema::table('produtos', function (Blueprint $table) {
            // ✅ Nova coluna: valor_atacado
            // - decimal(10,2): até 99.999.999,99
            // - nullable: produtos antigos não terão valor
            // - after('valor_unitario'): fica logo após o preço unitário
            $table->decimal('valor_atacado', 10, 2)
                  ->nullable()
                  ->after('valor_unitario')
                  ->comment('Preço de venda no atacado (exibido no frontend)');
        });
    }

    /**
     * Reverte a migration: REMOVE a coluna valor_atacado
     */
    public function down(): void
    {
        Schema::table('produtos', function (Blueprint $table) {
            $table->dropColumn('valor_atacado');
        });
    }
};