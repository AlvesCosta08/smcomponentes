<?php

namespace App\Models;

use App\Domain\Pedidos\Enums\StatusPedidoEnum;
use App\Domain\Pedidos\Enums\StatusPagamentoEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pedido extends Model
{
    use HasFactory;

    protected $table = 'pedidos';

    protected $fillable = [
        'user_id', 'numero_pedido', 'subtotal', 'desconto', 'total', 
        'status', 'forma_pagamento', 'status_pagamento', 'observacoes', 
        'endereco_entrega', 'cidade', 'estado', 'cep', 'data_pagamento', 
        'data_envio', 'data_entrega'
    ];

    // ✅ CASTS PARA ENUMS (DDD)
    protected $casts = [
        'subtotal' => 'decimal:2',
        'desconto' => 'decimal:2',
        'total' => 'decimal:2',
        'status' => StatusPedidoEnum::class,
        'status_pagamento' => StatusPagamentoEnum::class,
        'data_pagamento' => 'datetime',
        'data_envio' => 'datetime',
        'data_entrega' => 'datetime',
    ];

    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function itens(): HasMany { return $this->hasMany(PedidoItem::class); }

    // ✅ DELEGAÇÃO PARA O ENUM
    public function podeCancelar(): bool
    {
        return $this->status->canBeCanceled();
    }

    // ✅ ACCESSORS DE FORMATAÇÃO (Camada de Apresentação)
    public function getTotalFormatadoAttribute(): string
    {
        return 'R$ ' . number_format($this->total, 2, ',', '.');
    }

    public function getSubtotalFormatadoAttribute(): string
    {
        return 'R$ ' . number_format($this->subtotal, 2, ',', '.');
    }

    public function getDescontoFormatadoAttribute(): string
    {
        return 'R$ ' . number_format($this->desconto, 2, ',', '.');
    }

    public function getDataCriacaoFormatadaAttribute(): string
    {
        return $this->created_at ? $this->created_at->format('d/m/Y H:i') : '';
    }

    // ✅ SCOPES
    public function scopePendentes($query) { return $query->where('status', StatusPedidoEnum::PENDENTE); }
    public function scopePagos($query) { return $query->where('status', StatusPedidoEnum::PAGO); }
    public function scopeDoUsuario($query, $userId) { return $query->where('user_id', $userId); }

    /* 
     * NOTA ARQUITETURAL: 
     * O método gerarNumeroPedido() foi removido daqui. 
     * Essa é uma regra de Infraestrutura/Aplicação e deve estar em:
     * App\Domain\Pedidos\Services\OrderNumberGenerator
     */
}