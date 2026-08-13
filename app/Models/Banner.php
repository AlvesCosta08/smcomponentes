<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Banner extends Model
{
    protected $fillable = [
        'titulo',
        'subtitulo',
        'descricao',
        'imagem',
        'tipo',
        'cor_fundo',
        'cor_texto',
        'link',
        'texto_botao',
        'cor_botao',
        'ordem',
        'ativo',
        'inicio_em',
        'termino_em'
    ];

    protected $casts = [
        'ativo' => 'boolean',
        'inicio_em' => 'datetime',
        'termino_em' => 'datetime',
    ];

    // Scopes
    public function scopeAtivo($query)
    {
        return $query->where('ativo', true)
            ->where(function($q) {
                $q->whereNull('inicio_em')
                  ->orWhere('inicio_em', '<=', now());
            })
            ->where(function($q) {
                $q->whereNull('termino_em')
                  ->orWhere('termino_em', '>=', now());
            });
    }

    public function scopeOrdenado($query)
    {
        return $query->orderBy('ordem');
    }

    // Accessors
    public function getImagemUrlAttribute()
    {
        if ($this->imagem && Storage::disk('public')->exists($this->imagem)) {
            return Storage::url($this->imagem);
        }
        return null;
    }

    public function getEstiloFundoAttribute()
    {
        if (str_starts_with($this->cor_fundo, '#')) {
            return "background-color: {$this->cor_fundo};";
        }
        
        // Se for gradiente
        if (str_contains($this->cor_fundo, 'gradient')) {
            return "background: {$this->cor_fundo};";
        }
        
        return '';
    }
}