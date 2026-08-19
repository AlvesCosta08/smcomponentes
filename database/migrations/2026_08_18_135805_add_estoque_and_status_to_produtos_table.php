<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('produtos', function (Blueprint $table) {
            if (!Schema::hasColumn('produtos', 'estoque')) {
                $table->integer('estoque')->default(0)->after('quantidade');
            }
            if (!Schema::hasColumn('produtos', 'status')) {
                $table->string('status')->default('ativo')->after('estoque');
            }
        });
    }

    public function down(): void
    {
        Schema::table('produtos', function (Blueprint $table) {
            if (Schema::hasColumn('produtos', 'estoque')) {
                $table->dropColumn('estoque');
            }
            if (Schema::hasColumn('produtos', 'status')) {
                $table->dropColumn('status');
            }
        });
    }
};