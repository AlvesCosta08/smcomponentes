<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // ✅ Usa SQL direto (não precisa de doctrine/dbal)
        DB::statement('ALTER TABLE `produtos` MODIFY `referencia` VARCHAR(255) NULL DEFAULT NULL');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE `produtos` MODIFY `referencia` VARCHAR(255) NOT NULL');
    }
};