<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Produto extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'produtos';

    protected $fillable = [
        'categoria_id',
        'referencia',
        'descricao',
        'tipo',
        'status',
        'imagem',
        'slug',
        'quantidade',
        'estoque_minimo',
        'valor_unitario',
        'valor_atacado',
        'preco_promocional',
        'ativo',
        'destaque',
        'novo',
        'mais_vendido',
        'data_compra',
        'ultima_atualizacao_estoque',
        'visualizacoes',
        'ultima_visualizacao',
        'fornecedor',
    ];

    protected $casts = [
        'valor_unitario' => 'float',
        'valor_atacado' => 'float',
        'preco_promocional' => 'float',
        'quantidade' => 'integer',
        'estoque_minimo' => 'integer',
        'ativo' => 'boolean',
        'destaque' => 'boolean',
        'novo' => 'boolean',
        'mais_vendido' => 'boolean',
        'data_compra' => 'date',
        'ultima_atualizacao_estoque' => 'datetime',
        'ultima_visualizacao' => 'datetime',
        'visualizacoes' => 'integer',
    ];

    protected $appends = [
        'preco_formatado',
        'preco_atacado_formatado',
        'preco_promocional_formatado',
        'imagem_url',
        'imagens_urls',
        'status_label',
        'disponivel',
        'tem_promocao',
        'desconto_percentual',
        'pode_comprar',
    ];

    // ========== RELACIONAMENTOS ==========
    public function categoria()
    {
        return $this->belongsTo(Categoria::class);
    }

    public function imagens()
    {
        return $this->hasMany(ProdutoImagem::class)->orderBy('ordem');
    }

    public function imagemPrincipal()
    {
        return $this->hasOne(ProdutoImagem::class)->where('principal', true);
    }

    // ========== ACESSORS (GETTERS) ==========
    public function getPrecoFormatadoAttribute(): string
    {
        return 'R$ ' . number_format($this->valor_unitario ?? 0, 2, ',', '.');
    }

    public function getPrecoAtacadoFormatadoAttribute(): string
    {
        $valor = $this->valor_atacado ?? $this->valor_unitario ?? 0;
        return 'R$ ' . number_format($valor, 2, ',', '.');
    }

    public function getPrecoAtacadoAttribute(): float
    {
        return (float) ($this->attributes['valor_atacado'] ?? $this->valor_unitario ?? 0);
    }

    public function getPrecoPromocionalFormatadoAttribute(): ?string
    {
        return $this->preco_promocional
            ? 'R$ ' . number_format($this->preco_promocional, 2, ',', '.')
            : null;
    }

    public function getImagemUrlAttribute(): string
    {
        if (!empty($this->imagem)) {
            $path = 'produtos/' . basename($this->imagem);
            if (Storage::disk('public')->exists($path)) {
                return asset('storage/' . $path);
            }
        }
        if ($this->relationLoaded('imagens') && $this->imagens->isNotEmpty()) {
            $primeira = $this->imagens->first();
            if ($primeira && !empty($primeira->imagem)) {
                $path = 'produtos/' . basename($primeira->imagem);
                if (Storage::disk('public')->exists($path)) {
                    return asset('storage/' . $path);
                }
            }
        }
        return asset('images/produto-placeholder.jpg');
    }

    public function getImagensUrlsAttribute(): array
    {
        $urls = [];
        if ($this->relationLoaded('imagens') && $this->imagens->isNotEmpty()) {
            foreach ($this->imagens as $imagem) {
                if (!empty($imagem->imagem)) {
                    $path = 'produtos/' . basename($imagem->imagem);
                    if (Storage::disk('public')->exists($path)) {
                        $urls[] = asset('storage/' . $path);
                    }
                }
            }
        }
        if (empty($urls) && !empty($this->imagem)) {
            $path = 'produtos/' . basename($this->imagem);
            if (Storage::disk('public')->exists($path)) {
                $urls[] = asset('storage/' . $path);
            }
        }
        if (empty($urls)) {
            $urls[] = asset('images/produto-placeholder.jpg');
        }
        return $urls;
    }

    public function getStatusLabelAttribute(): string
    {
        if (!$this->ativo) return 'Inativo';
        return match ($this->status) {
            'disponivel' => 'Disponível',
            'indisponivel' => 'Indisponível',
            'sob_encomenda' => 'Sob Encomenda',
            default => 'Desconhecido',
        };
    }

    public function getDisponivelAttribute(): bool
    {
        return $this->isDisponivel();
    }

    public function getTemPromocaoAttribute(): bool
    {
        $precoBase = $this->preco_atacado;
        return $this->preco_promocional
            && $this->preco_promocional > 0
            && $this->preco_promocional < $precoBase;
    }

    public function getDescontoPercentualAttribute(): int
    {
        $precoBase = $this->preco_atacado;
        if (!$this->tem_promocao || $precoBase <= 0) return 0;
        return (int) round((($precoBase - $this->preco_promocional) / $precoBase) * 100);
    }

    public function getPodeComprarAttribute(): bool
    {
        return $this->isDisponivel();
    }

    // ========== MÉTODOS AUXILIARES ==========
    public function isDisponivel(): bool
    {
        return $this->ativo
            && ($this->quantidade ?? 0) > 0
            && $this->status === 'disponivel';
    }

    public function atualizarDisponibilidade(): void
    {
        if (!$this->ativo) {
            $this->status = 'indisponivel';
        } elseif ($this->quantidade <= 0) {
            $this->status = 'indisponivel';
        }
    }

    public function incrementarVisualizacoes(): void
    {
        $this->increment('visualizacoes');
        $this->ultima_visualizacao = now();
        $this->saveQuietly();
    }

    public function aumentarEstoque(int $quantidade): bool
    {
        $this->quantidade += $quantidade;
        $this->ultima_atualizacao_estoque = now();
        return $this->save();
    }

    public function reduzirEstoque(int $quantidade): bool
    {
        if ($this->quantidade < $quantidade) return false;
        $this->quantidade -= $quantidade;
        $this->ultima_atualizacao_estoque = now();
        return $this->save();
    }

    public function temEstoque(int $quantidade): bool
    {
        return $this->quantidade >= $quantidade;
    }

    // ========== SCOPES ==========
    public function scopeDisponivel($query)
    {
        return $query->where('ativo', true)
                     ->where('status', 'disponivel')
                     ->where('quantidade', '>', 0);
    }

    public function scopeEmDestaque($query)
    {
        return $query->disponivel()->where('destaque', true);
    }

    public function scopeOfertas($query)
    {
        return $query->disponivel()
                     ->whereNotNull('preco_promocional')
                     ->where('preco_promocional', '>', 0)
                     ->whereRaw('preco_promocional < COALESCE(valor_atacado, valor_unitario)');
    }

    public function scopeNovos($query)
    {
        return $query->disponivel()->where('novo', true)->orderBy('created_at', 'desc');
    }

    public function scopeMaisVendidos($query)
    {
        return $query->disponivel()->where('mais_vendido', true)->orderBy('visualizacoes', 'desc');
    }

    public function scopeBaixoEstoque($query, int $limite = 5)
    {
        return $query->where('ativo', true)
                     ->where('quantidade', '<=', $limite)
                     ->where('quantidade', '>', 0)
                     ->orderBy('quantidade');
    }

    public function scopeBuscar($query, string $termo)
    {
        return $query->where(function ($q) use ($termo) {
            $q->where('descricao', 'LIKE', "%{$termo}%")
              ->orWhere('referencia', 'LIKE', "%{$termo}%")
              ->orWhereHas('categoria', fn($q) => $q->where('nome', 'LIKE', "%{$termo}%"));
        });
    }

    // ========== BOOT ==========
    protected static function booted()
    {
        static::creating(function ($produto) {
            if (empty($produto->slug)) {
                $base = Str::slug($produto->descricao);
                $slug = $base;
                $counter = 1;
                while (static::where('slug', $slug)->exists()) {
                    $slug = $base . '-' . $counter++;
                }
                $produto->slug = $slug;
            }
            $produto->status ??= 'indisponivel';
            $produto->visualizacoes ??= 0;
            $produto->estoque_minimo ??= 5;

            if (empty($produto->valor_atacado) && !empty($produto->valor_unitario)) {
                $produto->valor_atacado = $produto->valor_unitario;
            }
        });

        static::updating(function ($produto) {
            if ($produto->isDirty('descricao') && empty($produto->slug)) {
                $base = Str::slug($produto->descricao);
                $slug = $base;
                $counter = 1;
                while (static::where('slug', $slug)->where('id', '!=', $produto->id)->exists()) {
                    $slug = $base . '-' . $counter++;
                }
                $produto->slug = $slug;
            }

            if ($produto->isDirty('quantidade')) {
                $produto->ultima_atualizacao_estoque = now();
                if ($produto->quantidade <= 0 && $produto->ativo) {
                    $produto->status = 'indisponivel';
                }
            }
        });
    }
}