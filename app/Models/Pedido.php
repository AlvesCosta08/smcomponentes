<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pedido extends Model
{
    use HasFactory;

    protected $table = 'pedidos';

    protected $fillable = [
        'user_id',
        'numero_pedido',
        'subtotal',
        'desconto',
        'total',
        'status',
        'forma_pagamento',
        'status_pagamento',
        'observacoes',
        'endereco_entrega',
        'cidade',
        'estado',
        'cep',
        'data_pagamento',
        'data_envio',
        'data_entrega'
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'desconto' => 'decimal:2',
        'total' => 'decimal:2',
        'data_pagamento' => 'datetime',
        'data_envio' => 'datetime',
        'data_entrega' => 'datetime',
    ];

    // ===== RELACIONAMENTOS =====
    
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function itens(): HasMany
    {
        return $this->hasMany(PedidoItem::class);
    }

    // ===== MÉTODOS DE STATUS =====
    
    public function isPendente(): bool
    {
        return $this->status === 'pendente';
    }

    public function isPago(): bool
    {
        return $this->status === 'pago';
    }

    public function isProcessando(): bool
    {
        return $this->status === 'processando';
    }

    public function isEnviado(): bool
    {
        return $this->status === 'enviado';
    }

    public function isEntregue(): bool
    {
        return $this->status === 'entregue';
    }

    public function isCancelado(): bool
    {
        return $this->status === 'cancelado';
    }

    public function podeCancelar(): bool
    {
        return in_array($this->status, ['pendente', 'pago']);
    }

    // ===== STATUS LABELS =====
    
    public static function statusLabels(): array
    {
        return [
            'pendente' => 'Pendente',
            'pago' => 'Pago',
            'processando' => 'Processando',
            'enviado' => 'Enviado',
            'entregue' => 'Entregue',
            'cancelado' => 'Cancelado'
        ];
    }

    public function getStatusLabelAttribute(): string
    {
        return self::statusLabels()[$this->status] ?? $this->status;
    }

    public static function statusColors(): array
    {
        return [
            'pendente' => 'warning',
            'pago' => 'info',
            'processando' => 'primary',
            'enviado' => 'success',
            'entregue' => 'success',
            'cancelado' => 'danger'
        ];
    }

    public function getStatusColorAttribute(): string
    {
        return self::statusColors()[$this->status] ?? 'secondary';
    }

    public static function statusIcons(): array
    {
        return [
            'pendente' => 'bi-clock',
            'pago' => 'bi-credit-card',
            'processando' => 'bi-arrow-repeat',
            'enviado' => 'bi-truck',
            'entregue' => 'bi-check-circle',
            'cancelado' => 'bi-x-circle'
        ];
    }

    public function getStatusIconAttribute(): string
    {
        return self::statusIcons()[$this->status] ?? 'bi-circle';
    }

    // ===== MÉTODO PARA GERAR NÚMERO DO PEDIDO =====
    
    public static function gerarNumeroPedido(): string
    {
        $prefix = 'PED-' . date('Ymd') . '-';
        $last = self::where('numero_pedido', 'like', $prefix . '%')
            ->orderBy('id', 'desc')
            ->first();
        
        if ($last) {
            $lastNumber = intval(substr($last->numero_pedido, -4));
            $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '0001';
        }
        
        return $prefix . $newNumber;
    }

    // ===== CALCULAR TOTAL =====
    
    public function calcularTotal(): void
    {
        $subtotal = $this->itens->sum('subtotal');
        $this->subtotal = $subtotal;
        $this->total = $subtotal - $this->desconto;
        $this->save();
    }

    // ===== MÉTODOS DE FORMATAÇÃO =====
    
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
        return $this->created_at->format('d/m/Y H:i');
    }

    // ===== SCOPES =====
    
    public function scopePendentes($query)
    {
        return $query->where('status', 'pendente');
    }

    public function scopePagos($query)
    {
        return $query->where('status', 'pago');
    }

    public function scopeProcessando($query)
    {
        return $query->where('status', 'processando');
    }

    public function scopeEnviados($query)
    {
        return $query->where('status', 'enviado');
    }

    public function scopeEntregues($query)
    {
        return $query->where('status', 'entregue');
    }

    public function scopeCancelados($query)
    {
        return $query->where('status', 'cancelado');
    }

    public function scopeDoUsuario($query, $userId)
    {
        return $query->where('user_id', $userId);
    }
}