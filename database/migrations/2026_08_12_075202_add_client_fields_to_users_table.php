<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Verificar se a tabela users existe
        if (!Schema::hasTable('users')) {
            return;
        }

        Schema::table('users', function (Blueprint $table) {
            // Verificar se a coluna existe antes de adicionar
            $columns = Schema::getColumnListing('users');
            
            if (!in_array('cnpj', $columns)) {
                $table->string('cnpj')->nullable()->after('cpf');
            }
            
            if (!in_array('ie', $columns)) {
                $table->string('ie')->nullable()->after('cnpj');
            }
            
            if (!in_array('telefone', $columns)) {
                $table->string('telefone')->nullable()->after('ie');
            }
            
            if (!in_array('celular', $columns)) {
                $table->string('celular')->nullable()->after('telefone');
            }
            
            if (!in_array('logradouro', $columns)) {
                $table->string('logradouro')->nullable()->after('celular');
            }
            
            if (!in_array('numero', $columns)) {
                $table->string('numero')->nullable()->after('logradouro');
            }
            
            if (!in_array('bairro', $columns)) {
                $table->string('bairro')->nullable()->after('numero');
            }
            
            if (!in_array('complemento', $columns)) {
                $table->string('complemento')->nullable()->after('bairro');
            }
            
            if (!in_array('cidade', $columns)) {
                $table->string('cidade')->nullable()->after('complemento');
            }
            
            if (!in_array('estado', $columns)) {
                $table->string('estado', 2)->nullable()->after('cidade');
            }
            
            if (!in_array('cep', $columns)) {
                $table->string('cep', 10)->nullable()->after('estado');
            }
            
            if (!in_array('ativo', $columns)) {
                $table->boolean('ativo')->default(true)->after('cep');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $columns = Schema::getColumnListing('users');
            
            $columnsToDrop = ['cnpj', 'ie', 'telefone', 'celular', 
                            'logradouro', 'numero', 'bairro', 'complemento',
                            'cidade', 'estado', 'cep', 'ativo'];
            
            foreach ($columnsToDrop as $column) {
                if (in_array($column, $columns)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};