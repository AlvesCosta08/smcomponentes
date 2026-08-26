<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Banner extends Model
{
    use HasFactory;

    protected $fillable = [
        'titulo',
        'subtitulo',
        'descricao',
        'imagem',
        'link',
        'texto_botao',
        'cor_texto',
        'cor_botao',
        'cor_fundo',
        'ativo',
        'ordem',
        'inicio_em',
        'termino_em'
    ];

    protected $casts = [
        'ativo' => 'boolean',
        'inicio_em' => 'datetime',
        'termino_em' => 'datetime',
    ];

    protected $appends = ['imagem_url', 'estilo_fundo'];

    /**
     * Accessor para URL da imagem
     */
    public function getImagemUrlAttribute(): ?string
    {
        if (empty($this->imagem)) {
            return null;
        }

        // Se já for uma URL completa, retorna
        if (filter_var($this->imagem, FILTER_VALIDATE_URL)) {
            return $this->imagem;
        }

        // Remove prefixos indesejados
        $path = str_replace(['storage/', 'banners/'], '', $this->imagem);
        $path = ltrim($path, '/');
        
        // Tenta encontrar a imagem em diferentes locais
        $pathsToTry = [
            'banners/' . $path,
            'banners/' . basename($this->imagem),
            $this->imagem,
        ];

        foreach ($pathsToTry as $tryPath) {
            $tryPath = ltrim($tryPath, '/');
            if (Storage::disk('public')->exists($tryPath)) {
                return asset('storage/' . $tryPath);
            }
        }

        // Fallback: tenta via asset
        if (strpos($this->imagem, 'banners/') !== false) {
            return asset('storage/' . $this->imagem);
        }

        if (strpos($this->imagem, 'storage/') !== false) {
            return asset($this->imagem);
        }

        // Último fallback
        return asset('storage/banners/' . basename($this->imagem));
    }

    /**
     * Accessor para estilo de fundo
     */
    public function getEstiloFundoAttribute(): string
    {
        if (!empty($this->cor_fundo)) {
            $corFundo = trim($this->cor_fundo);
            
            if (str_starts_with($corFundo, '#')) {
                return "background-color: {$corFundo};";
            }
            
            if (str_contains($corFundo, 'gradient')) {
                return "background: {$corFundo};";
            }
            
            return "background: {$corFundo};";
        }
        
        return 'background: linear-gradient(135deg, #0b1a33 0%, #1a3a5c 100%);';
    }

    /**
     * Escopo para banners ativos
     */
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

    /**
     * Escopo para ordenação
     */
    public function scopeOrdenado($query)
    {
        return $query->orderBy('ordem', 'asc');
    }
}