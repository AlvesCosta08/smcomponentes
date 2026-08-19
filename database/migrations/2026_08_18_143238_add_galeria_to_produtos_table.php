<?php
// database/migrations/YYYY_MM_DD_HHMMSS_add_galeria_to_produtos_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('produtos', function (Blueprint $table) {
            // Adiciona campo galeria após imagem
            $table->json('galeria')->nullable()->after('imagem');
        });
    }

    public function down(): void
    {
        Schema::table('produtos', function (Blueprint $table) {
            $table->dropColumn('galeria');
        });
    }
};