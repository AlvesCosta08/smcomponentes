<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WishlistItem extends Model
{
    use HasFactory;

    protected $table = 'wishlist_items';

    protected $fillable = ['wishlist_id', 'produto_id', 'observacao', 'added_at'];

    protected $casts = [
        'added_at' => 'datetime',
    ];

    public function wishlist(): BelongsTo { return $this->belongsTo(Wishlist::class); }
    public function produto(): BelongsTo { return $this->belongsTo(Produto::class); }

    public function scopeRecent($query, int $days = 7)
    {
        return $query->where('added_at', '>=', now()->subDays($days));
    }
}