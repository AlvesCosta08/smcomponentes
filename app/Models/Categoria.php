<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Categoria extends Model
{
    use SoftDeletes;

    protected $table = 'categorias';

    protected $fillable = [
        'nome',
        'slug',
        'descricao',
        'imagem',
        'ativo',
        'ordem',
        'categoria_pai_id',
    ];

    protected $casts = [
        'ativo' => 'boolean',
        'ordem' => 'integer',
    ];

    protected $appends = [
        'imagem_url',
        'nome_completo',
    ];

    // ==============================================
    // RELACIONAMENTOS
    // ==============================================

    public function produtos()
    {
        return $this->hasMany(Produto::class);
    }

    public function produtosDisponiveis()
    {
        return $this->hasMany(Produto::class)->disponivel();
    }

    public function subcategorias()
    {
        return $this->hasMany(Categoria::class, 'categoria_pai_id');
    }

    public function categoriaPai()
    {
        return $this->belongsTo(Categoria::class, 'categoria_pai_id');
    }

    // ==============================================
    // ACCESSORS
    // ==============================================

    public function getImagemUrlAttribute(): string
    {
        if (!$this->imagem) {
            return asset('images/categoria-placeholder.jpg');
        }

        if (filter_var($this->imagem, FILTER_VALIDATE_URL)) {
            return $this->imagem;
        }

        if (\Storage::disk('public')->exists('categorias/' . $this->imagem)) {
            return asset('storage/categorias/' . $this->imagem);
        }

        return asset('images/categoria-placeholder.jpg');
    }

    public function getNomeCompletoAttribute(): string
    {
        if ($this->categoriaPai) {
            return $this->categoriaPai->nome . ' > ' . $this->nome;
        }
        return $this->nome;
    }

    // ==============================================
    // SCOPES
    // ==============================================

    public function scopeAtivo($query)
    {
        return $query->where('ativo', true);
    }

    public function scopeOrdenado($query)
    {
        return $query->orderBy('ordem')->orderBy('nome');
    }

    public function scopePai($query)
    {
        return $query->whereNull('categoria_pai_id');
    }

    public function scopeFilhas($query, $paiId)
    {
        return $query->where('categoria_pai_id', $paiId);
    }

    // ==============================================
    // MÉTODOS
    // ==============================================

    public function isAtivo(): bool
    {
        return (bool) $this->ativo;
    }

    public function getContagemProdutos(): int
    {
        return $this->produtos()->disponivel()->count();
    }

    // ==============================================
    // BOOT
    // ==============================================

    protected static function booted()
    {
        static::creating(function ($categoria) {
            if (empty($categoria->slug)) {
                $categoria->slug = Str::slug($categoria->nome);
            }
        });

        static::updating(function ($categoria) {
            if ($categoria->isDirty('nome') && empty($categoria->slug)) {
                $categoria->slug = Str::slug($categoria->nome);
            }
        });
    }
}