<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Produto extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'produtos';

    protected $fillable = [
        'descricao',
        'categoria',
        'valor_unitario',
        'valor_atacado', // 🔥 ADICIONADO
        'preco_promocional',
        'quantidade',
        'estoque',
        'disponibilidade',
        'ativo',
        'slug',
        'imagem',
        'referencia',
        'visualizacoes',
        'categoria_id',
        'destaque',
        'novo',
        'mais_vendido',
        'ipi',
    ];

    protected $casts = [
        'ativo' => 'boolean',
        'valor_unitario' => 'decimal:2',
        'valor_atacado' => 'decimal:2', // 🔥 ADICIONADO
        'preco_promocional' => 'decimal:2',
        'quantidade' => 'integer',
        'visualizacoes' => 'integer',
        'ipi' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    protected $appends = [
        'preco_formatado',
        'preco_atacado_formatado', // 🔥 NOVO
        'preco_promocional_formatado',
        'preco_com_ipi',
        'preco_com_ipi_formatado',
    ];

    /**
     * 🔥 Accessor: Preço Unitário formatado
     */
    public function getPrecoFormatadoAttribute(): string
    {
        return 'R$ ' . number_format($this->valor_unitario, 2, ',', '.');
    }

    /**
     * 🔥 Accessor: Preço Atacado formatado
     */
    public function getPrecoAtacadoFormatadoAttribute(): string
    {
        return 'R$ ' . number_format($this->valor_atacado ?? 0, 2, ',', '.');
    }

    /**
     * 🔥 Accessor: Preço Promocional formatado
     */
    public function getPrecoPromocionalFormatadoAttribute(): string
    {
        if ($this->preco_promocional) {
            return 'R$ ' . number_format($this->preco_promocional, 2, ',', '.');
        }
        return '';
    }

    /**
     * 🔥 Accessor: Preço com IPI (calculado sobre o valor_atacado)
     */
    public function getPrecoComIpiAttribute(): float
    {
        // Usa valor_atacado como base (preço de venda no atacado)
        $base = $this->valor_atacado ?? $this->valor_unitario;
        
        if (!$base || $base <= 0) {
            return 0;
        }
        
        $ipi = $this->ipi ?? 9.75;
        return round($base * (1 + ($ipi / 100)), 2);
    }

    /**
     * 🔥 Accessor: Preço com IPI formatado
     */
    public function getPrecoComIpiFormatadoAttribute(): string
    {
        return 'R$ ' . number_format($this->preco_com_ipi, 2, ',', '.');
    }

    /**
     * 🔥 Accessor: IPI formatado para exibição
     */
    public function getIpiFormatadoAttribute(): string
    {
        return number_format($this->ipi ?? 0, 2) . '%';
    }

    /**
     * 🔥 Accessor: Verifica se o produto tem IPI
     */
    public function getTemIpiAttribute(): bool
    {
        return ($this->ipi ?? 0) > 0;
    }

    /**
     * 🔥 Accessor: Verifica se tem preço promocional
     */
    public function getTemPromocaoAttribute(): bool
    {
        return $this->preco_promocional !== null 
            && $this->preco_promocional > 0 
            && $this->preco_promocional < $this->valor_atacado;
    }

    /**
     * 🔥 Accessor: URL da imagem
     */
    public function getImagemUrlAttribute(): string
    {
        if ($this->imagem) {
            return asset('storage/produtos/' . $this->imagem);
        }
        return asset('images/produto-placeholder.jpg');
    }

    /**
     * 🔥 Accessor: Status de disponibilidade
     */
    public function getStatusAttribute(): string
    {
        if (!$this->ativo) {
            return 'Inativo';
        }
        if ($this->quantidade <= 0) {
            return 'Esgotado';
        }
        if ($this->disponibilidade === 'INDISPONÍVEL') {
            return 'Indisponível';
        }
        return 'Disponível';
    }

    // ==============================================
    // Escopos
    // ==============================================

    /**
     * 🔥 Escopo para produtos disponíveis (TODOS)
     */
    public function scopeDisponivel($query)
    {
        return $query->where('ativo', true)
            ->where('disponibilidade', 'DISPONÍVEL')
            ->where('quantidade', '>', 0);
    }

    /**
     * 🔥 Escopo para listar todos os produtos disponíveis
     */
    public function scopeTodosDisponiveis($query)
    {
        return $query->disponivel()
            ->orderBy('created_at', 'desc');
    }

    /**
     * 🔥 Escopo para produtos em destaque
     */
    public function scopeEmDestaque($query)
    {
        return $query->disponivel()
            ->where('destaque', true)
            ->orderBy('created_at', 'desc');
    }

    /**
     * 🔥 Escopo para produtos em oferta (com preço promocional)
     */
    public function scopeOfertas($query)
    {
        return $query->disponivel()
            ->whereNotNull('preco_promocional')
            ->where('preco_promocional', '>', 0)
            ->orderBy('created_at', 'desc');
    }

    /**
     * 🔥 Escopo para produtos novos
     */
    public function scopeNovos($query)
    {
        return $query->disponivel()
            ->where('novo', true)
            ->orderBy('created_at', 'desc');
    }

    /**
     * 🔥 Escopo para produtos mais vendidos
     */
    public function scopeMaisVendidos($query)
    {
        return $query->disponivel()
            ->where('mais_vendido', true)
            ->orderBy('visualizacoes', 'desc');
    }

    /**
     * 🔥 Escopo para produtos com baixo estoque
     */
    public function scopeBaixoEstoque($query, $limite = 5)
    {
        return $query->where('quantidade', '<=', $limite)
            ->where('ativo', true);
    }

    /**
     * 🔥 Escopo para buscar produtos
     */
    public function scopeBuscar($query, $termo)
    {
        return $query->where(function($q) use ($termo) {
            $q->where('descricao', 'ILIKE', "%{$termo}%")
              ->orWhere('categoria', 'ILIKE', "%{$termo}%")
              ->orWhere('referencia', 'ILIKE', "%{$termo}%");
        });
    }

    // ==============================================
    // Métodos
    // ==============================================

    /**
     * 🔥 Método: Verifica se o produto está disponível
     */
    public function isDisponivel(): bool
    {
        return $this->ativo 
            && $this->disponibilidade === 'DISPONÍVEL'
            && $this->quantidade > 0;
    }

    /**
     * 🔥 Método: Verifica se tem estoque
     */
    public function temEstoque(int $quantidade = 1): bool
    {
        return $this->quantidade >= $quantidade;
    }

    /**
     * 🔥 Método: Reduzir estoque
     */
    public function reduzirEstoque(int $quantidade): bool
    {
        if (!$this->temEstoque($quantidade)) {
            return false;
        }
        
        $this->quantidade -= $quantidade;
        
        if ($this->quantidade <= 0) {
            $this->disponibilidade = 'INDISPONÍVEL';
        }
        
        return $this->save();
    }

    /**
     * 🔥 Método: Aumentar estoque
     */
    public function aumentarEstoque(int $quantidade): bool
    {
        $this->quantidade += $quantidade;
        
        if ($this->disponibilidade === 'INDISPONÍVEL' && $this->quantidade > 0) {
            $this->disponibilidade = 'DISPONÍVEL';
        }
        
        return $this->save();
    }

    /**
     * 🔥 Método: Incrementar visualizações
     */
    public function incrementarVisualizacoes(): void
    {
        $this->increment('visualizacoes');
    }

    /**
     * 🔥 Relacionamento com PedidoItem
     */
    public function itensPedido()
    {
        return $this->hasMany(PedidoItem::class);
    }
}