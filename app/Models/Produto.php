<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class Produto extends Model
{
    protected $fillable = [
        'descricao',
        'categoria',
        'valor_unitario',
        'preco_promocional',
        'quantidade',        // Nome da coluna
        'estoque',          // Se tiver estoque
        'disponibilidade',   // DISPONÍVEL / INDISPONÍVEL
        'ativo',            // true / false
        'slug',
        'imagem',
        'referencia',       // Se existir
        'visualizacoes'     // Se existir
    ];

    protected $casts = [
        'ativo' => 'boolean',
        'valor_unitario' => 'decimal:2',
        'preco_promocional' => 'decimal:2',
    ];

    /**
     * 🔥 Escopo para produtos disponíveis
     */
    public function scopeDisponivel($query)
    {
        return $query->where('ativo', true)
            ->where('disponibilidade', 'DISPONÍVEL')
            ->where('quantidade', '>', 0);
    }

    /**
     * 🔥 Escopo para produtos em destaque
     */
    public function scopeEmDestaque($query)
    {
        return $query->disponivel()
            ->orderBy('created_at', 'desc');
    }
}