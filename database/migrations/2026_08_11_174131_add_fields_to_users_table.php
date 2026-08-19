<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Verifica se a coluna não existe antes de adicionar
            if (!Schema::hasColumn('users', 'telefone')) {
                $table->string('telefone')->nullable()->after('email');
            }
            if (!Schema::hasColumn('users', 'cpf')) {
                $table->string('cpf')->unique()->nullable()->after('telefone');
            }
            if (!Schema::hasColumn('users', 'data_nascimento')) {
                $table->date('data_nascimento')->nullable()->after('cpf');
            }
            if (!Schema::hasColumn('users', 'cep')) {
                $table->string('cep')->nullable()->after('data_nascimento');
            }
            if (!Schema::hasColumn('users', 'logradouro')) {
                $table->string('logradouro')->nullable()->after('cep');
            }
            if (!Schema::hasColumn('users', 'numero')) {
                $table->string('numero')->nullable()->after('logradouro');
            }
            if (!Schema::hasColumn('users', 'complemento')) {
                $table->string('complemento')->nullable()->after('numero');
            }
            if (!Schema::hasColumn('users', 'bairro')) {
                $table->string('bairro')->nullable()->after('complemento');
            }
            if (!Schema::hasColumn('users', 'cidade')) {
                $table->string('cidade')->nullable()->after('bairro');
            }
            if (!Schema::hasColumn('users', 'estado')) {
                $table->string('estado', 2)->nullable()->after('cidade');
            }
            if (!Schema::hasColumn('users', 'ativo')) {
                $table->boolean('ativo')->default(true)->after('estado');
            }
            if (!Schema::hasColumn('users', 'ultimo_acesso')) {
                $table->timestamp('ultimo_acesso')->nullable()->after('ativo');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $columns = [
                'telefone', 'cpf', 'data_nascimento', 
                'cep', 'logradouro', 'numero', 'complemento',
                'bairro', 'cidade', 'estado',
                'ativo', 'ultimo_acesso'
            ];
            
            foreach ($columns as $column) {
                if (Schema::hasColumn('users', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
