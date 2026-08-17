<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // 🔥 Verifica e adiciona apenas se não existir
            if (!Schema::hasColumn('users', 'ie')) {
                $table->string('ie')->nullable()->after('cpf');
                echo "✅ Coluna 'ie' adicionada com sucesso!\n";
            } else {
                echo "ℹ️ Coluna 'ie' já existe. Pulando...\n";
            }
            
            if (!Schema::hasColumn('users', 'celular')) {
                $table->string('celular')->nullable()->after('telefone');
                echo "✅ Coluna 'celular' adicionada com sucesso!\n";
            } else {
                echo "ℹ️ Coluna 'celular' já existe. Pulando...\n";
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // 🔥 Remove apenas se existir
            if (Schema::hasColumn('users', 'ie')) {
                $table->dropColumn('ie');
                echo "✅ Coluna 'ie' removida com sucesso!\n";
            }
            
            if (Schema::hasColumn('users', 'celular')) {
                $table->dropColumn('celular');
                echo "✅ Coluna 'celular' removida com sucesso!\n";
            }
        });
    }
};