<?php
// app/Models/WishlistItem.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WishlistItem extends Model
{
    use HasFactory;

    protected $table = 'wishlist_items';

    protected $fillable = [
        'wishlist_id',
        'produto_id',
        'observacao',
        'added_at',
    ];

    protected $casts = [
        'added_at' => 'datetime',
    ];

    /**
     * Relacionamento com a wishlist
     */
    public function wishlist(): BelongsTo
    {
        return $this->belongsTo(Wishlist::class);
    }

    /**
     * Relacionamento com o produto
     */
    public function produto(): BelongsTo
    {
        return $this->belongsTo(Produto::class);
    }

    /**
     * Escopo para itens adicionados recentemente
     */
    public function scopeRecent($query, int $days = 7)
    {
        return $query->where('added_at', '>=', now()->subDays($days));
    }

    /**
     * Obter o preço atual do produto
     */
    public function getPrecoAtualAttribute(): float
    {
        return $this->produto->preco ?? 0;
    }

    /**
     * Verificar se o produto está em promoção
     */
    public function getEmPromocaoAttribute(): bool
    {
        return $this->produto && $this->produto->preco_promocional > 0;
    }

    /**
     * Obter a imagem do produto
     */
    public function getImagemAttribute(): ?string
    {
        return $this->produto->imagem ?? null;
    }

    /**
     * Obter o nome do produto
     */
    public function getNomeProdutoAttribute(): string
    {
        return $this->produto->descricao ?? 'Produto não encontrado';
    }
}