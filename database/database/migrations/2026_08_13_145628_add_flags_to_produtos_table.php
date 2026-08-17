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
            // Verifica se a coluna não existe antes de criar, para evitar erros
            if (!Schema::hasColumn('produtos', 'destaque')) {
                $table->boolean('destaque')->default(false)->after('ativo');
            }
            
            if (!Schema::hasColumn('produtos', 'novo')) {
                $table->boolean('novo')->default(false)->after('destaque');
            }
            
            if (!Schema::hasColumn('produtos', 'mais_vendido')) {
                $table->boolean('mais_vendido')->default(false)->after('novo');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('produtos', function (Blueprint $table) {
            $table->dropColumn(['destaque', 'novo', 'mais_vendido']);
        });
    }
};