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
        'pedido_id',
        'produto_id',
        'quantidade',
        'preco_unitario',
        'preco_promocional',
        'subtotal',
        'nome_produto',
        'imagem_produto',
        'variacao'
    ];

    protected $casts = [
        'preco_unitario' => 'decimal:2',
        'preco_promocional' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'quantidade' => 'integer'
    ];

    // ===== RELACIONAMENTOS =====
    
    public function pedido(): BelongsTo
    {
        return $this->belongsTo(Pedido::class);
    }

    public function produto(): BelongsTo
    {
        return $this->belongsTo(Produto::class);
    }

    // ===== MÉTODOS ÚTEIS =====
    
    public function getPrecoExibicaoAttribute(): float
    {
        return $this->preco_promocional ?? $this->preco_unitario;
    }

    public function getPrecoFormatadoAttribute(): string
    {
        return 'R$ ' . number_format($this->preco_unitario, 2, ',', '.');
    }

    public function getPrecoPromocionalFormatadoAttribute(): string
    {
        return $this->preco_promocional ? 'R$ ' . number_format($this->preco_promocional, 2, ',', '.') : null;
    }

    public function getSubtotalFormatadoAttribute(): string
    {
        return 'R$ ' . number_format($this->subtotal, 2, ',', '.');
    }

    public function getPrecoExibicaoFormatadoAttribute(): string
    {
        return 'R$ ' . number_format($this->getPrecoExibicaoAttribute(), 2, ',', '.');
    }

    // ===== SCOPES =====
    
    public function scopeDoPedido($query, $pedidoId)
    {
        return $query->where('pedido_id', $pedidoId);
    }

    public function scopeDoProduto($query, $produtoId)
    {
        return $query->where('produto_id', $produtoId);
    }
}