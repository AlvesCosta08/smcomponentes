<?php
// database/migrations/xxxx_xx_xx_create_wishlists_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wishlists', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('nome')->default('Minha Lista de Desejos');
            $table->boolean('is_default')->default(false);
            $table->boolean('is_public')->default(false);
            $table->text('descricao')->nullable();
            $table->timestamps();
            
            $table->index('user_id');
            $table->index('is_default');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wishlists');
    }
};