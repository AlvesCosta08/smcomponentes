<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('produtos', function (Blueprint $table) {
            $columns = [
                'categoria',
                'disponibilidade',
                'estoque',
                'valor_atacado',
                'valor_compra',
                'valor_custo',
                'ipi',
                'percentual_custo',
                'margem_lucro',
                'galeria',
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn('produtos', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }

    public function down(): void
    {
        Schema::table('produtos', function (Blueprint $table) {
            if (!Schema::hasColumn('produtos', 'categoria')) {
                $table->string('categoria')->nullable();
            }
            if (!Schema::hasColumn('produtos', 'disponibilidade')) {
                $table->string('disponibilidade')->nullable();
            }
            if (!Schema::hasColumn('produtos', 'estoque')) {
                $table->integer('estoque')->nullable();
            }
            if (!Schema::hasColumn('produtos', 'valor_atacado')) {
                $table->decimal('valor_atacado', 10, 2)->nullable();
            }
            if (!Schema::hasColumn('produtos', 'valor_compra')) {
                $table->decimal('valor_compra', 10, 2)->nullable();
            }
            if (!Schema::hasColumn('produtos', 'valor_custo')) {
                $table->decimal('valor_custo', 10, 2)->nullable();
            }
            if (!Schema::hasColumn('produtos', 'ipi')) {
                $table->decimal('ipi', 5, 2)->nullable();
            }
            if (!Schema::hasColumn('produtos', 'percentual_custo')) {
                $table->decimal('percentual_custo', 5, 2)->nullable();
            }
            if (!Schema::hasColumn('produtos', 'margem_lucro')) {
                $table->decimal('margem_lucro', 5, 2)->nullable();
            }
            if (!Schema::hasColumn('produtos', 'galeria')) {
                $table->json('galeria')->nullable();
            }
        });
    }
};