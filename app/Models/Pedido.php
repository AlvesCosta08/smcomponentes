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

    // Relacionamentos
    public function user(): BelongsTo 
    { 
        return $this->belongsTo(User::class); 
    }
    
    public function itens(): HasMany 
    { 
        return $this->hasMany(PedidoItem::class); 
    }

    /**
     * Gera um número de pedido único
     * Formato: PED-YYYYMMDD-XXXX
     */
    public static function gerarNumeroPedido(): string
    {
        $prefix = 'PED-' . date('Ymd');
        $last = self::where('numero_pedido', 'LIKE', $prefix . '%')
            ->orderBy('numero_pedido', 'desc')
            ->first();
        
        if (!$last) {
            return $prefix . '-0001';
        }
        
        $lastNumber = (int) substr($last->numero_pedido, -4);
        $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        
        return $prefix . '-' . $newNumber;
    }

    /**
     * Verifica se o pedido pode ser cancelado
     * Delega a verificação para o enum de status
     */
    public function podeCancelar(): bool
    {
        // Verifica se o status do pedido permite cancelamento
        if (!$this->status->canBeCanceled()) {
            return false;
        }

        // Verifica se o status do pagamento permite cancelamento
        if ($this->status_pagamento instanceof StatusPagamentoEnum) {
            return $this->status_pagamento->canBeCanceled();
        }

        // Fallback: se não for um enum, verifica o valor
        return in_array($this->status_pagamento, [
            'aguardando',
            'cancelado',
            'pendente'
        ]);
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

    // ✅ ACCESSORS PARA A VIEW
    public function getStatusLabelAttribute(): string
    {
        return $this->status instanceof StatusPedidoEnum 
            ? $this->status->label() 
            : ucfirst($this->status);
    }

    public function getStatusColorAttribute(): string
    {
        return $this->status instanceof StatusPedidoEnum 
            ? $this->status->color() 
            : 'secondary';
    }

    public function getStatusIconAttribute(): string
    {
        return $this->status instanceof StatusPedidoEnum 
            ? $this->status->icon() 
            : 'bi-circle';
    }

    public function getPagamentoLabelAttribute(): string
    {
        return $this->status_pagamento instanceof StatusPagamentoEnum 
            ? $this->status_pagamento->label() 
            : ucfirst($this->status_pagamento);
    }

    public function getPagamentoColorAttribute(): string
    {
        return $this->status_pagamento instanceof StatusPagamentoEnum 
            ? $this->status_pagamento->color() 
            : 'secondary';
    }

    // ✅ SCOPES
    public function scopePendentes($query) 
    { 
        return $query->where('status', StatusPedidoEnum::PENDENTE->value); 
    }
    
    public function scopePagos($query) 
    { 
        return $query->where('status', StatusPedidoEnum::PAGO->value); 
    }
    
    public function scopeDoUsuario($query, $userId) 
    { 
        return $query->where('user_id', $userId); 
    }
}