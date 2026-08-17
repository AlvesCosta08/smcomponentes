<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pedidos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('numero_pedido')->unique();
            $table->decimal('subtotal', 10, 2)->default(0);
            $table->decimal('desconto', 10, 2)->default(0);
            $table->decimal('total', 10, 2);
            $table->enum('status', [
                'pendente', 
                'pago', 
                'processando', 
                'enviado', 
                'entregue', 
                'cancelado'
            ])->default('pendente');
            $table->string('forma_pagamento')->nullable();
            $table->string('status_pagamento')->default('aguardando');
            $table->text('observacoes')->nullable();
            $table->string('endereco_entrega')->nullable();
            $table->string('cidade')->nullable();
            $table->string('estado', 2)->nullable();
            $table->string('cep', 10)->nullable();
            $table->timestamp('data_pagamento')->nullable();
            $table->timestamp('data_envio')->nullable();
            $table->timestamp('data_entrega')->nullable();
            $table->timestamps();
            
            // Índices
            $table->index('user_id');
            $table->index('status');
            $table->index('numero_pedido');
            $table->index('created_at'); // ✅ ADICIONADO para consultas de data
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pedidos');
    }
};