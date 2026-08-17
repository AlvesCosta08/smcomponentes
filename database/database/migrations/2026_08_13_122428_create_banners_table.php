<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('banners', function (Blueprint $table) {
            $table->id();
            
            // Dados do banner
            $table->string('titulo')->nullable();
            $table->string('subtitulo')->nullable();
            $table->text('descricao')->nullable();
            $table->string('imagem')->nullable(); // Caminho da imagem
            
            // Configurações
            $table->string('tipo')->default('imagem'); // imagem, texto, misto
            $table->string('cor_fundo')->nullable(); // #hex ou gradiente
            $table->string('cor_texto')->default('#ffffff');
            
            // Link
            $table->string('link')->nullable();
            $table->string('texto_botao')->nullable();
            $table->string('cor_botao')->nullable();
            
            // Ordenação e status
            $table->integer('ordem')->default(0);
            $table->boolean('ativo')->default(true);
            
            // Datas de exibição (opcional)
            $table->timestamp('inicio_em')->nullable();
            $table->timestamp('termino_em')->nullable();
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('banners');
    }
};