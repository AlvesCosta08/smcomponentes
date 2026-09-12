<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Remove índices órfãos ANTES de dropar colunas
        Schema::table('produtos', function (Blueprint $table) {
            foreach (['produtos_categoria_index'] as $indice) {
                try {
                    $table->dropIndex($indice);
                } catch (\Throwable $e) {
                    // índice não existe — ok
                }
            }
        });

        // 2. Dropa cada coluna individualmente
        $colunas = [
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

        foreach ($colunas as $coluna) {
            if (Schema::hasColumn('produtos', $coluna)) {
                Schema::table('produtos', function (Blueprint $table) use ($coluna) {
                    $table->dropColumn($coluna);
                });
            }
        }

        // 3. Adiciona coluna status se não existir
        if (!Schema::hasColumn('produtos', 'status')) {
            Schema::table('produtos', function (Blueprint $table) {
                $table->enum('status', ['disponivel', 'indisponivel', 'sob_encomenda'])
                      ->default('indisponivel')
                      ->after('tipo');
            });
        }
    }

    public function down(): void
    {
        Schema::table('produtos', function (Blueprint $table) {
            if (!Schema::hasColumn('produtos', 'categoria')) {
                $table->string('categoria')->nullable();
            }
            if (!Schema::hasColumn('produtos', 'disponibilidade')) {
                $table->string('disponibilidade')->default('INDISPONIVEL');
            }
            if (!Schema::hasColumn('produtos', 'estoque')) {
                $table->integer('estoque')->default(0);
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
            if (Schema::hasColumn('produtos', 'status')) {
                $table->dropColumn('status');
            }
        });
    }
};