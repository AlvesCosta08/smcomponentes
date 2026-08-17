<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pedido_itens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pedido_id')->constrained()->onDelete('cascade');
            $table->foreignId('produto_id')->constrained()->onDelete('cascade');
            $table->integer('quantidade')->default(1);
            $table->decimal('preco_unitario', 10, 2);
            $table->decimal('preco_promocional', 10, 2)->nullable();
            $table->decimal('subtotal', 10, 2);
            $table->string('nome_produto')->nullable(); // Snapshot do nome
            $table->string('imagem_produto')->nullable(); // Snapshot da imagem
            $table->timestamps();
            
            // Índices
            $table->index('pedido_id');
            $table->index('produto_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pedido_itens');
    }
};