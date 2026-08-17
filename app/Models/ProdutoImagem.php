<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProdutoImagem extends Model
{
    protected $table = 'produto_imagens';

    protected $fillable = [
        'produto_id',
        'imagem',
        'ordem',
        'principal',
    ];

    protected $casts = [
        'principal' => 'boolean',
        'ordem' => 'integer',
    ];

    protected $appends = [
        'imagem_url',
    ];

    // ==============================================
    // RELACIONAMENTOS
    // ==============================================

    public function produto()
    {
        return $this->belongsTo(Produto::class);
    }

    // ==============================================
    // ACCESSORS
    // ==============================================

    public function getImagemUrlAttribute(): string
    {
        if (!$this->imagem) {
            return asset('images/produto-placeholder.jpg');
        }

        if (filter_var($this->imagem, FILTER_VALIDATE_URL)) {
            return $this->imagem;
        }

        if (\Storage::disk('public')->exists('produtos/' . $this->imagem)) {
            return asset('storage/produtos/' . $this->imagem);
        }

        return asset('images/produto-placeholder.jpg');
    }

    // ==============================================
    // SCOPES
    // ==============================================

    public function scopePrincipal($query)
    {
        return $query->where('principal', true);
    }

    public function scopeOrdenado($query)
    {
        return $query->orderBy('ordem')->orderBy('id');
    }
}