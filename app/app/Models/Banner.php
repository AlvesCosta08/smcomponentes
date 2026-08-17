<?php
// app/Models/Banner.php

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

    /**
     * Accessor CORRIGIDO - Retorna a URL completa da imagem
     */
    public function getImagemUrlAttribute()
    {
        if (!$this->imagem) {
            return null;
        }

        // Se já for URL completa (http/https)
        if (filter_var($this->imagem, FILTER_VALIDATE_URL)) {
            return $this->imagem;
        }

        // Se for caminho do storage
        if (Storage::disk('public')->exists($this->imagem)) {
            return asset('storage/' . $this->imagem);
        }

        // Se for caminho direto na pasta public
        if (file_exists(public_path($this->imagem))) {
            return asset($this->imagem);
        }

        return null;
    }

    public function getEstiloFundoAttribute()
    {
        if (!$this->cor_fundo) {
            return 'background: linear-gradient(135deg, #0b1a33 0%, #1a3a5c 100%);';
        }
        
        if (str_starts_with($this->cor_fundo, '#')) {
            return "background-color: {$this->cor_fundo};";
        }
        
        // Se for gradiente
        if (str_contains($this->cor_fundo, 'gradient')) {
            return "background: {$this->cor_fundo};";
        }
        
        return "background: {$this->cor_fundo};";
    }
}