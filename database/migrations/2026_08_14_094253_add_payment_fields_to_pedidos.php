<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pedidos', function (Blueprint $table) {
            // ============================================
            // ADICIONAR APENAS OS CAMPOS QUE FALTAM
            // ============================================
            
            // boleto_url
            if (!Schema::hasColumn('pedidos', 'boleto_url')) {
                $table->string('boleto_url')->nullable()->after('observacoes');
            }
            
            // qr_code
            if (!Schema::hasColumn('pedidos', 'qr_code')) {
                $table->text('qr_code')->nullable()->after('boleto_url');
            }
            
            // qr_code_base64
            if (!Schema::hasColumn('pedidos', 'qr_code_base64')) {
                $table->text('qr_code_base64')->nullable()->after('qr_code');
            }
            
            // ticket_url
            if (!Schema::hasColumn('pedidos', 'ticket_url')) {
                $table->string('ticket_url')->nullable()->after('qr_code_base64');
            }
            
            // barcode
            if (!Schema::hasColumn('pedidos', 'barcode')) {
                $table->string('barcode')->nullable()->after('ticket_url');
            }
        });
    }

    public function down(): void
    {
        Schema::table('pedidos', function (Blueprint $table) {
            $columns = [
                'boleto_url',
                'qr_code',
                'qr_code_base64',
                'ticket_url',
                'barcode',
            ];
            
            foreach ($columns as $column) {
                if (Schema::hasColumn('pedidos', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};