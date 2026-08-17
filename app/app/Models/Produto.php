<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Produto extends Model
{
    use HasFactory, SoftDeletes;

    // ==============================================
    // CONSTANTES
    // ==============================================
    
    const DISPONIVEL = 'DISPONIVEL';
    const INDISPONIVEL = 'INDISPONIVEL';
    const ESTOQUE_BAIXO = 'EST.BAIXO';

    const STATUS_LABELS = [
        self::DISPONIVEL => 'Disponível',
        self::INDISPONIVEL => 'Indisponível',
        self::ESTOQUE_BAIXO => 'Estoque Baixo',
    ];

    // ==============================================
    // TABELA E FILLABLE
    // ==============================================

    protected $table = 'produtos';

    protected $fillable = [
        'descricao',
        'slug',
        'referencia',
        'categoria_id',
        'valor_unitario',
        'valor_atacado',
        'preco_promocional',
        'ipi',
        'quantidade',
        'disponibilidade',
        'ativo',
        'destaque',
        'novo',
        'mais_vendido',
        'imagem',
        'visualizacoes',
    ];

    // ==============================================
    // CASTS
    // ==============================================

    protected $casts = [
        'valor_unitario' => 'decimal:2',
        'valor_atacado' => 'decimal:2',
        'preco_promocional' => 'decimal:2',
        'ipi' => 'decimal:2',
        'quantidade' => 'integer',
        'visualizacoes' => 'integer',
        'ativo' => 'boolean',
        'destaque' => 'boolean',
        'novo' => 'boolean',
        'mais_vendido' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    // ==============================================
    // APPENDS
    // ==============================================

    protected $appends = [
        'preco_formatado',
        'preco_atacado_formatado',
        'preco_promocional_formatado',
        'preco_com_ipi',
        'preco_com_ipi_formatado',
        'imagem_url',
        'status_label',
        'disponivel',
        'tem_promocao',
        'tem_ipi',
    ];

    // ==============================================
    // RELACIONAMENTOS
    // ==============================================

    public function categoria()
    {
        return $this->belongsTo(Categoria::class);
    }

    public function imagens()
    {
        return $this->hasMany(ProdutoImagem::class);
    }

    public function itensPedido()
    {
        return $this->hasMany(PedidoItem::class);
    }

    public function wishlistItems()
    {
        return $this->hasMany(WishlistItem::class);
    }

    // ==============================================
    // ACCESSORS - PREÇOS
    // ==============================================

    public function getPrecoFormatadoAttribute(): string
    {
        return 'R$ ' . number_format($this->valor_unitario, 2, ',', '.');
    }

    public function getPrecoAtacadoFormatadoAttribute(): string
    {
        if ($this->valor_atacado) {
            return 'R$ ' . number_format($this->valor_atacado, 2, ',', '.');
        }
        return $this->getPrecoFormatadoAttribute();
    }

    public function getPrecoPromocionalFormatadoAttribute(): string
    {
        if ($this->preco_promocional && $this->preco_promocional > 0) {
            return 'R$ ' . number_format($this->preco_promocional, 2, ',', '.');
        }
        return '';
    }

    public function getPrecoComIpiAttribute(): float
    {
        $base = $this->valor_atacado ?? $this->valor_unitario;
        
        if (!$base || $base <= 0) {
            return 0;
        }
        
        $ipi = $this->ipi ?? 0;
        return round($base * (1 + ($ipi / 100)), 2);
    }

    public function getPrecoComIpiFormatadoAttribute(): string
    {
        return 'R$ ' . number_format($this->preco_com_ipi, 2, ',', '.');
    }

    public function getIpiFormatadoAttribute(): string
    {
        return number_format($this->ipi ?? 0, 2) . '%';
    }

    public function getTemIpiAttribute(): bool
    {
        return ($this->ipi ?? 0) > 0;
    }

    public function getTemPromocaoAttribute(): bool
    {
        return $this->preco_promocional !== null 
            && $this->preco_promocional > 0 
            && $this->preco_promocional < ($this->valor_atacado ?? $this->valor_unitario);
    }

    // ==============================================
    // ACCESSORS - IMAGEM
    // ==============================================

    public function getImagemUrlAttribute(): string
    {
        if (!$this->imagem) {
            return asset('images/produto-placeholder.jpg');
        }

        // Se for URL externa
        if (filter_var($this->imagem, FILTER_VALIDATE_URL)) {
            return $this->imagem;
        }

        // Se existir no storage
        if (\Storage::disk('public')->exists('produtos/' . $this->imagem)) {
            return asset('storage/produtos/' . $this->imagem);
        }

        return asset('images/produto-placeholder.jpg');
    }

    // ==============================================
    // ACCESSORS - STATUS
    // ==============================================

    public function getStatusLabelAttribute(): string
    {
        if (!$this->ativo) {
            return 'Inativo';
        }
        return self::STATUS_LABELS[$this->disponibilidade] ?? 'Desconhecido';
    }

    public function getDisponivelAttribute(): bool
    {
        return $this->isDisponivel();
    }

    // ==============================================
    // SCOPES
    // ==============================================

    public function scopeDisponivel($query)
    {
        return $query->where('ativo', true)
            ->where('disponibilidade', self::DISPONIVEL)
            ->where('quantidade', '>', 0);
    }

    public function scopeEmDestaque($query)
    {
        return $query->disponivel()
            ->where('destaque', true)
            ->orderBy('created_at', 'desc');
    }

    public function scopeOfertas($query)
    {
        return $query->disponivel()
            ->whereNotNull('preco_promocional')
            ->where('preco_promocional', '>', 0)
            ->where('preco_promocional', '<', \DB::raw('COALESCE(valor_atacado, valor_unitario)'));
    }

    public function scopeNovos($query)
    {
        return $query->disponivel()
            ->where('novo', true)
            ->orderBy('created_at', 'desc');
    }

    public function scopeMaisVendidos($query)
    {
        return $query->disponivel()
            ->where('mais_vendido', true)
            ->orderBy('visualizacoes', 'desc');
    }

    public function scopeBaixoEstoque($query, $limite = 5)
    {
        return $query->where('ativo', true)
            ->where('quantidade', '<=', $limite)
            ->where('quantidade', '>', 0)
            ->orderBy('quantidade', 'asc');
    }

    public function scopePorCategoria($query, $categoriaId)
    {
        return $query->where('categoria_id', $categoriaId);
    }

    public function scopeBuscar($query, $termo)
    {
        return $query->where(function($q) use ($termo) {
            $q->where('descricao', 'ILIKE', "%{$termo}%")
              ->orWhere('referencia', 'ILIKE', "%{$termo}%")
              ->orWhereHas('categoria', function($q) use ($termo) {
                  $q->where('nome', 'ILIKE', "%{$termo}%");
              });
        });
    }

    public function scopeAtivos($query)
    {
        return $query->where('ativo', true);
    }

    public function scopeInativos($query)
    {
        return $query->where('ativo', false);
    }

    // ==============================================
    // MÉTODOS
    // ==============================================

    public function isDisponivel(): bool
    {
        return $this->ativo 
            && $this->disponibilidade === self::DISPONIVEL
            && $this->quantidade > 0;
    }

    public function temEstoque(int $quantidade = 1): bool
    {
        return $this->quantidade >= $quantidade;
    }

    public function reduzirEstoque(int $quantidade): bool
    {
        if (!$this->temEstoque($quantidade)) {
            return false;
        }
        
        $this->quantidade -= $quantidade;
        $this->atualizarDisponibilidade();
        
        return $this->save();
    }

    public function aumentarEstoque(int $quantidade): bool
    {
        $this->quantidade += $quantidade;
        $this->atualizarDisponibilidade();
        
        return $this->save();
    }

    public function atualizarDisponibilidade(): void
    {
        if (!$this->ativo) {
            $this->disponibilidade = self::INDISPONIVEL;
        } elseif ($this->quantidade <= 0) {
            $this->disponibilidade = self::INDISPONIVEL;
        } elseif ($this->quantidade <= 5) {
            $this->disponibilidade = self::ESTOQUE_BAIXO;
        } else {
            $this->disponibilidade = self::DISPONIVEL;
        }
    }

    public function incrementarVisualizacoes(): void
    {
        $this->increment('visualizacoes');
    }

    public function getPrecoVenda(): float
    {
        if ($this->tem_promocao) {
            return $this->preco_promocional;
        }
        
        return $this->valor_atacado ?? $this->valor_unitario;
    }

    public function getPrecoVendaFormatado(): string
    {
        return 'R$ ' . number_format($this->getPrecoVenda(), 2, ',', '.');
    }

    public function getDescontoPercentual(): float
    {
        if (!$this->tem_promocao) {
            return 0;
        }
        
        $base = $this->valor_atacado ?? $this->valor_unitario;
        
        if ($base <= 0) {
            return 0;
        }
        
        return round((($base - $this->preco_promocional) / $base) * 100, 0);
    }

    // ==============================================
    // BOOT
    // ==============================================

    protected static function booted()
    {
        static::creating(function ($produto) {
            if (empty($produto->slug)) {
                $produto->slug = Str::slug($produto->descricao);
            }
            
            if (empty($produto->disponibilidade)) {
                $produto->disponibilidade = self::DISPONIVEL;
            }
        });

        static::updating(function ($produto) {
            if ($produto->isDirty('descricao') && empty($produto->slug)) {
                $produto->slug = Str::slug($produto->descricao);
            }
            
            if ($produto->isDirty('quantidade') || $produto->isDirty('ativo')) {
                $produto->atualizarDisponibilidade();
            }
        });
    }
}