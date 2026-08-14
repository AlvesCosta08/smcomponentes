<?php
// app/Models/WishlistItem.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WishlistItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'wishlist_id',
        'produto_id',
        'observacao',
    ];

    /**
     * Relacionamento com wishlist
     */
    public function wishlist()
    {
        return $this->belongsTo(Wishlist::class);
    }

    /**
     * Relacionamento com produto
     */
    public function produto()
    {
        return $this->belongsTo(Produto::class);
    }
}