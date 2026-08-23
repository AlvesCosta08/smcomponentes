<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PedidoItem extends Model
{
    use HasFactory;

    protected $table = 'pedido_itens';

    protected $fillable = [
        'pedido_id', 'produto_id', 'quantidade', 'preco_unitario', 
        'preco_promocional', 'subtotal', 'nome_produto', 'imagem_produto', 'variacao'
    ];

    protected $casts = [
        'preco_unitario' => 'decimal:2',
        'preco_promocional' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'quantidade' => 'integer'
    ];

    public function pedido(): BelongsTo { return $this->belongsTo(Pedido::class); }
    public function produto(): BelongsTo { return $this->belongsTo(Produto::class); }

    public function getPrecoExibicaoAttribute(): float
    {
        return $this->preco_promocional ?? $this->preco_unitario;
    }

    public function getPrecoFormatadoAttribute(): string
    {
        return 'R$ ' . number_format($this->preco_unitario, 2, ',', '.');
    }

    public function getSubtotalFormatadoAttribute(): string
    {
        return 'R$ ' . number_format($this->subtotal, 2, ',', '.');
    }

    public function scopeDoPedido($query, $pedidoId) { return $query->where('pedido_id', $pedidoId); }
}