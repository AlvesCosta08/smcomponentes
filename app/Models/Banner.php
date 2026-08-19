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
     * 
     * Esta função verifica:
     * 1. Se a imagem é uma URL completa (http/https)
     * 2. Se a imagem existe na pasta storage/app/public/banners/
     * 3. Se a imagem existe diretamente no storage
     * 4. Se a imagem existe na pasta public/
     */
    public function getImagemUrlAttribute()
    {
        // Se não tiver imagem, retorna null
        if (!$this->imagem) {
            return null;
        }

        // Se já for URL completa (http/https)
        if (filter_var($this->imagem, FILTER_VALIDATE_URL)) {
            return $this->imagem;
        }

        // Verificar se a imagem existe na pasta banners/
        // Caminho: storage/app/public/banners/nome_da_imagem.jpg
        $path = 'banners/' . $this->imagem;
        if (Storage::disk('public')->exists($path)) {
            return asset('storage/' . $path);
        }

        // Verificar se a imagem existe diretamente no storage
        // Caminho: storage/app/public/nome_da_imagem.jpg
        if (Storage::disk('public')->exists($this->imagem)) {
            return asset('storage/' . $this->imagem);
        }

        // Se for caminho direto na pasta public
        // Caminho: public/nome_da_imagem.jpg
        if (file_exists(public_path($this->imagem))) {
            return asset($this->imagem);
        }

        // Se não encontrar em nenhum lugar, retorna null
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