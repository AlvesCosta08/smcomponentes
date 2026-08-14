<?php
// app/Models/Wishlist.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Wishlist extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'nome',
        'is_default',
        'is_public',
        'descricao',
    ];

    protected $casts = [
        'is_default' => 'boolean',
        'is_public' => 'boolean',
    ];

    /**
     * Relacionamento com usuário
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relacionamento com itens
     */
    public function items()
    {
        return $this->hasMany(WishlistItem::class);
    }

    /**
     * Relacionamento com produtos via itens
     */
    public function produtos()
    {
        return $this->belongsToMany(Produto::class, 'wishlist_items')
            ->withTimestamps()
            ->withPivot('observacao');
    }

    /**
     * Escopo para listas padrão
     */
    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }

    /**
     * Escopo para listas públicas
     */
    public function scopePublic($query)
    {
        return $query->where('is_public', true);
    }

    /**
     * Verificar se o produto está na lista
     */
    public function hasProduct(int $produtoId): bool
    {
        return $this->items()->where('produto_id', $produtoId)->exists();
    }

    /**
     * Adicionar produto à lista
     */
    public function addProduct(Produto $produto, ?string $observacao = null): WishlistItem
    {
        return $this->items()->create([
            'produto_id' => $produto->id,
            'observacao' => $observacao,
        ]);
    }

    /**
     * Remover produto da lista
     */
    public function removeProduct(Produto $produto): bool
    {
        return $this->items()->where('produto_id', $produto->id)->delete();
    }

    /**
     * Contar itens da lista
     */
    public function countItems(): int
    {
        return $this->items()->count();
    }
}