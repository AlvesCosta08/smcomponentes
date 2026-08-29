<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Carrinho extends Model
{
    use HasFactory;

    protected $table = 'carrinhos';

    protected $fillable = [
        'user_id',
        'produto_id',
        'quantidade',
        'session_id'
    ];

    protected $casts = [
        'quantidade' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function produto()
    {
        return $this->belongsTo(Produto::class);
    }
}