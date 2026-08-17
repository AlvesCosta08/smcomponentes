<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('produtos', function (Blueprint $table) {
            $table->id();
            
            // Categorias e Identificação
            $table->string('categoria')->index();
            $table->string('referencia')->unique(); // SKU/Código
            $table->string('descricao');
            $table->string('tipo')->nullable();
            $table->string('disponibilidade')->default('INDISPONIVEL');
            $table->string('imagem')->nullable();
            
            // Slug para URL amigável
            $table->string('slug')->unique()->nullable();
            
            // Estoque
            $table->integer('quantidade')->default(0);
            $table->integer('estoque_minimo')->default(5);
            
            // Valores Financeiros
            $table->decimal('valor_atacado', 10, 2)->nullable();
            $table->decimal('valor_compra', 10, 2)->nullable();
            $table->decimal('valor_unitario', 10, 2)->nullable();
            $table->decimal('valor_custo', 10, 2)->nullable();
            $table->decimal('preco_promocional', 10, 2)->nullable();
            $table->decimal('ipi', 5, 2)->nullable();
            $table->decimal('percentual_custo', 5, 2)->nullable();
            $table->decimal('margem_lucro', 5, 2)->nullable();
            
            // Status
            $table->boolean('ativo')->default(true);
            $table->boolean('destaque')->default(false);
            
            // Datas
            $table->date('data_compra')->nullable();
            $table->timestamp('ultima_atualizacao_estoque')->nullable();
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('produtos');
    }
};