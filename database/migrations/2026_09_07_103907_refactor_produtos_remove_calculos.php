<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('produtos', function (Blueprint $table) {
            // Remover colunas calculadas/redundantes
            $table->dropColumn([
                'categoria',            // substituída por categoria_id
                'disponibilidade',      // será substituída por status (enum)
                'estoque',              // duplicada com quantidade
                'valor_atacado',
                'valor_compra',
                'valor_custo',
                'ipi',
                'percentual_custo',
                'margem_lucro',
                'galeria',              // usamos tabela produto_imagens
            ]);

            // Adicionar campo de status (se não existir)
            if (!Schema::hasColumn('produtos', 'status')) {
                $table->enum('status', ['disponivel', 'indisponivel', 'sob_encomenda'])
                      ->default('indisponivel')
                      ->after('tipo');
            }
        });
    }

    public function down(): void
    {
        Schema::table('produtos', function (Blueprint $table) {
            // Reverter (opcional, apenas para rollback)
            $table->string('categoria')->nullable();
            $table->string('disponibilidade')->default('INDISPONIVEL');
            $table->integer('estoque')->default(0);
            $table->decimal('valor_atacado', 10, 2)->nullable();
            $table->decimal('valor_compra', 10, 2)->nullable();
            $table->decimal('valor_custo', 10, 2)->nullable();
            $table->decimal('ipi', 5, 2)->nullable();
            $table->decimal('percentual_custo', 5, 2)->nullable();
            $table->decimal('margem_lucro', 5, 2)->nullable();
            $table->json('galeria')->nullable();
            $table->dropColumn('status');
        });
    }
};