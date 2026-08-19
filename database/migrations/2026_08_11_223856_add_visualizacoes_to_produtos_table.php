<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('produtos', function (Blueprint $table) {
            // 🔥 Adiciona campo de visualizações
            $table->integer('visualizacoes')->default(0)->after('destaque');
            
            // 🔥 Adiciona campo para data da última visualização
            $table->timestamp('ultima_visualizacao')->nullable()->after('visualizacoes');
            
            // 🔥 Adiciona índice para consultas rápidas
            $table->index('visualizacoes');
            
            // 🔥 Adiciona campo para rating (avaliação)
            $table->decimal('rating', 3, 2)->default(0)->after('ultima_visualizacao');
            
            // 🔥 Adiciona campo para número de avaliações
            $table->integer('total_avaliacoes')->default(0)->after('rating');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('produtos', function (Blueprint $table) {
            $table->dropColumn([
                'visualizacoes',
                'ultima_visualizacao',
                'rating',
                'total_avaliacoes'
            ]);
        });
    }
};