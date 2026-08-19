<?php
// app/Models/Wishlist.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Wishlist extends Model
{
    use HasFactory;

    protected $table = 'wishlists';

    protected $fillable = [
        'user_id',
        'nome',
        'descricao',
        'is_default',
        'is_public',
    ];

    protected $casts = [
        'is_default' => 'boolean',
        'is_public' => 'boolean',
    ];

    /**
     * Relacionamento com o usuário
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relacionamento com os itens da wishlist
     */
    public function items(): HasMany
    {
        return $this->hasMany(WishlistItem::class);
    }

    /**
     * Relacionamento com produtos através dos itens
     */
    public function produtos(): BelongsToMany
    {
        return $this->belongsToMany(Produto::class, 'wishlist_items', 'wishlist_id', 'produto_id')
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
    public function addProduct(int $produtoId, ?string $observacao = null): WishlistItem
    {
        // Verificar se já existe
        if ($this->hasProduct($produtoId)) {
            throw new \Exception('Produto já está na wishlist');
        }

        return $this->items()->create([
            'produto_id' => $produtoId,
            'observacao' => $observacao,
        ]);
    }

    /**
     * Remover produto da lista
     */
    public function removeProduct(int $produtoId): bool
    {
        return $this->items()->where('produto_id', $produtoId)->delete() > 0;
    }

    /**
     * Contar itens da lista
     */
    public function countItems(): int
    {
        return $this->items()->count();
    }

    /**
     * Limpar todos os itens da lista
     */
    public function clearItems(): bool
    {
        return $this->items()->delete() > 0;
    }

    /**
     * Obter produtos com detalhes
     */
    public function getProductsWithDetails()
    {
        return $this->produtos()
            ->select('produtos.*')
            ->with(['categoria', 'imagens'])
            ->get();
    }

    /**
     * Verificar se é a lista padrão
     */
    public function isDefault(): bool
    {
        return (bool) $this->is_default;
    }

    /**
     * Tornar esta lista a padrão
     */
    public function setAsDefault(): bool
    {
        // Remover default de outras listas do usuário
        $this->where('user_id', $this->user_id)
            ->where('id', '!=', $this->id)
            ->update(['is_default' => false]);

        // Definir esta como default
        $this->is_default = true;
        return $this->save();
    }
}