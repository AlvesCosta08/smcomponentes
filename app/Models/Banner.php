<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Banner extends Model
{
    protected $fillable = [
        'titulo', 'subtitulo', 'descricao', 'imagem', 'tipo', 'cor_fundo', 
        'cor_texto', 'link', 'texto_botao', 'cor_botao', 'ordem', 'ativo', 
        'inicio_em', 'termino_em'
    ];

    protected $casts = [
        'ativo' => 'boolean',
        'inicio_em' => 'datetime',
        'termino_em' => 'datetime',
    ];

    public function scopeAtivo($query)
    {
        return $query->where('ativo', true)
            ->where(function($q) {
                $q->whereNull('inicio_em')->orWhere('inicio_em', '<=', now());
            })
            ->where(function($q) {
                $q->whereNull('termino_em')->orWhere('termino_em', '>=', now());
            });
    }

    public function scopeOrdenado($query)
    {
        return $query->orderBy('ordem');
    }

    public function getImagemUrlAttribute(): ?string
    {
        if (!$this->imagem) return null;
        if (filter_var($this->imagem, FILTER_VALIDATE_URL)) return $this->imagem;
        
        $path = 'banners/' . $this->imagem;
        if (Storage::disk('public')->exists($path)) {
            return asset('storage/' . $path);
        }
        
        return Storage::disk('public')->exists($this->imagem) 
            ? asset('storage/' . $this->imagem) 
            : asset('images/banner-placeholder.jpg');
    }

    public function getEstiloFundoAttribute(): string
    {
        if (!$this->cor_fundo) return 'background: linear-gradient(135deg, #0b1a33 0%, #1a3a5c 100%);';
        if (str_starts_with($this->cor_fundo, '#')) return "background-color: {$this->cor_fundo};";
        if (str_contains($this->cor_fundo, 'gradient')) return "background: {$this->cor_fundo};";
        
        return "background: {$this->cor_fundo};";
    }
}