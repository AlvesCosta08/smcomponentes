<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Esta migration é redundante com a 2026_09_07_103907_refactor_produtos_remove_calculos.
     * As colunas e índices já foram removidos na migration anterior.
     * Mantida como no-op para preservar o histórico.
     */
    public function up(): void
    {
        // No-op: colunas já removidas em refactor_produtos_remove_calculos
    }

    public function down(): void
    {
        // No-op: nada a reverter
    }
};