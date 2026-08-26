<?php

namespace App\Models;

use App\Domain\Produtos\Services\PricingCalculator;
use App\Domain\Produtos\ValueObjects\Stock;
use App\Enums\DisponibilidadeEnum;
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
        'categoria',
        'categoria_id',
        'referencia',
        'descricao',
        'tipo',
        'disponibilidade',
        'imagem',
        'slug',
        'quantidade',
        'estoque_minimo',
        'valor_atacado',
        'valor_compra',
        'valor_unitario',
        'valor_custo',
        'preco_promocional',
        'ipi',
        'percentual_custo',
        'margem_lucro',
        'ativo',
        'destaque',
        'novo',
        'mais_vendido',
        'data_compra',
        'ultima_atualizacao_estoque',
        'visualizacoes',
        'ultima_visualizacao',
    ];

    protected $casts = [
        'valor_atacado' => 'float',
        'valor_compra' => 'float',
        'valor_unitario' => 'float',
        'valor_custo' => 'float',
        'preco_promocional' => 'float',
        'ipi' => 'float',
        'percentual_custo' => 'float',
        'margem_lucro' => 'float',
        'quantidade' => 'integer',
        'estoque_minimo' => 'integer',
        'ativo' => 'boolean',
        'destaque' => 'boolean',
        'novo' => 'boolean',
        'mais_vendido' => 'boolean',
        'data_compra' => 'date',
        'ultima_atualizacao_estoque' => 'datetime',
        'ultima_visualizacao' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
        'disponibilidade' => DisponibilidadeEnum::class,
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
        'lucro_bruto_formatado',
        'pode_comprar',
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
        return $this->hasMany(ProdutoImagem::class)->orderBy('ordem', 'asc');
    }

    public function imagemPrincipal()
    {
        return $this->hasOne(ProdutoImagem::class)->where('principal', true);
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
    // ACESSORS (GETTERS)
    // ==============================================

    public function getPrecoFormatadoAttribute(): string
    {
        return 'R$ ' . number_format($this->valor_atacado ?? 0, 2, ',', '.');
    }

    public function getPrecoAtacadoFormatadoAttribute(): string
    {
        return 'R$ ' . number_format($this->valor_atacado ?? 0, 2, ',', '.');
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
            $filename = basename($this->imagem);
            $path = 'produtos/' . $filename;
            if (Storage::disk('public')->exists($path)) {
                return asset('storage/' . $path);
            }
            $altPath = 'images/' . $filename;
            if (Storage::disk('public')->exists($altPath)) {
                return asset('storage/' . $altPath);
            }
        }

        if ($this->relationLoaded('imagens') && $this->imagens->isNotEmpty()) {
            $primeiraImagem = $this->imagens->first();
            if ($primeiraImagem && !empty($primeiraImagem->imagem)) {
                $filename = basename($primeiraImagem->imagem);
                $path = 'produtos/' . $filename;
                if (Storage::disk('public')->exists($path)) {
                    return asset('storage/' . $path);
                }
                $altPath = 'images/' . $filename;
                if (Storage::disk('public')->exists($altPath)) {
                    return asset('storage/' . $altPath);
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
                    $filename = basename($imagem->imagem);
                    $path = 'produtos/' . $filename;
                    if (Storage::disk('public')->exists($path)) {
                        $urls[] = asset('storage/' . $path);
                        continue;
                    }
                    $altPath = 'images/' . $filename;
                    if (Storage::disk('public')->exists($altPath)) {
                        $urls[] = asset('storage/' . $altPath);
                    }
                }
            }
        }

        if (empty($urls) && !empty($this->imagem)) {
            $filename = basename($this->imagem);
            $path = 'produtos/' . $filename;
            if (Storage::disk('public')->exists($path)) {
                $urls[] = asset('storage/' . $path);
            } else {
                $altPath = 'images/' . $filename;
                if (Storage::disk('public')->exists($altPath)) {
                    $urls[] = asset('storage/' . $altPath);
                }
            }
        }

        if (empty($urls)) {
            $urls[] = asset('images/produto-placeholder.jpg');
        }

        return $urls;
    }

    public function getStatusLabelAttribute(): string
    {
        if (!$this->ativo) {
            return 'Inativo';
        }
        return $this->disponibilidade?->label() ?? 'Desconhecido';
    }

    public function getDisponivelAttribute(): bool
    {
        return $this->isDisponivel();
    }

    public function getTemPromocaoAttribute(): bool
    {
        return $this->hasPromocao();
    }

    public function getDescontoPercentualAttribute(): int
    {
        return $this->getDescontoPercentual();
    }

    public function getLucroBrutoFormatadoAttribute(): string
    {
        return $this->getLucroBrutoFormatado();
    }

    public function getPodeComprarAttribute(): bool
    {
        return $this->isDisponivel();
    }

    // ==============================================
    // MÉTODOS AUXILIARES
    // ==============================================

    public function isDisponivel(): bool
    {
        return $this->ativo 
            && ($this->quantidade ?? 0) > 0 
            && $this->disponibilidade === DisponibilidadeEnum::DISPONIVEL;
    }

    public function hasPromocao(): bool
    {
        return $this->preco_promocional !== null 
            && $this->preco_promocional > 0 
            && $this->preco_promocional < ($this->valor_atacado ?? 0);
    }

    public function getDescontoPercentual(): int
    {
        if (!$this->hasPromocao() || ($this->valor_atacado ?? 0) <= 0) {
            return 0;
        }
        return (int) round((($this->valor_atacado - $this->preco_promocional) / $this->valor_atacado) * 100);
    }

    public function getPrecoComIpi(): float
    {
        $preco = $this->valor_atacado ?? 0;
        $ipi = $this->ipi ?? 0;
        return round($preco * (1 + ($ipi / 100)), 2);
    }

    public function getPrecoComIpiFormatado(): string
    {
        return 'R$ ' . number_format($this->getPrecoComIpi(), 2, ',', '.');
    }

    public function getLucroBruto(): float
    {
        $preco = $this->valor_atacado ?? 0;
        $custo = $this->valor_custo ?? 0;
        return round($preco - $custo, 2);
    }

    public function getLucroBrutoFormatado(): string
    {
        return 'R$ ' . number_format($this->getLucroBruto(), 2, ',', '.');
    }

    public function atualizarDisponibilidade(): void
    {
        $stockVO = new Stock(
            (int) ($this->quantidade ?? 0),
            (int) ($this->estoque_minimo ?? 5)
        );
        
        if (!$this->ativo) {
            $this->disponibilidade = DisponibilidadeEnum::INDISPONIVEL;
        } else {
            $this->disponibilidade = $stockVO->getDisponibilidade();
        }
    }

    public function incrementarVisualizacoes(): void
    {
        $this->increment('visualizacoes');
        $this->ultima_visualizacao = now();
        $this->saveQuietly();
    }

    // ==============================================
    // SCOPES
    // ==============================================

    public function scopeDisponivel($query)
    {
        return $query->where('ativo', true)
            ->where('disponibilidade', DisponibilidadeEnum::DISPONIVEL->value)
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
            ->whereRaw('preco_promocional < valor_atacado');
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

    public function scopeBaixoEstoque($query, int $limite = 5)
    {
        return $query->where('ativo', true)
            ->where('quantidade', '<=', $limite)
            ->where('quantidade', '>', 0)
            ->orderBy('quantidade', 'asc');
    }

    public function scopeBuscar($query, string $termo)
    {
        return $query->where(function($q) use ($termo) {
            $q->where('descricao', 'LIKE', "%{$termo}%")
              ->orWhere('referencia', 'LIKE', "%{$termo}%")
              ->orWhere('categoria', 'LIKE', "%{$termo}%")
              ->orWhereHas('categoria', function($q) use ($termo) {
                  $q->where('nome', 'LIKE', "%{$termo}%");
              });
        });
    }

    // ==============================================
    // MÉTODOS ESTÁTICOS
    // ==============================================

    public static function getMargensDisponiveis(): array
    {
        return [
            20 => '20% - Lucro Baixo',
            30 => '30% - Lucro Médio',
            40 => '40% - Lucro Bom',
            50 => '50% - Lucro Ótimo',
            60 => '60% - Lucro Excelente',
            80 => '80% - Lucro Premium',
            100 => '100% - Lucro Máximo',
        ];
    }

    // ==============================================
    // BOOT
    // ==============================================

    protected static function booted()
    {
        static::creating(function ($produto) {
            if (empty($produto->slug)) {
                $baseSlug = Str::slug($produto->descricao);
                $slug = $baseSlug;
                $counter = 1;
                
                while (static::where('slug', $slug)->exists()) {
                    $slug = $baseSlug . '-' . $counter;
                    $counter++;
                }
                $produto->slug = $slug;
            }
            
            $produto->disponibilidade ??= DisponibilidadeEnum::DISPONIVEL->value;
            $produto->visualizacoes ??= 0;
            $produto->estoque_minimo ??= 5;

            if (!empty($produto->valor_compra)) {
                $calculator = new PricingCalculator();
                $resultados = $calculator->calculate(
                    (float) $produto->valor_compra,
                    (float) ($produto->margem_lucro ?? 80),
                    (float) ($produto->ipi ?? 0)
                );
                
                $produto->valor_custo = $resultados['valor_custo'];
                $produto->valor_atacado = $resultados['valor_atacado'];
                $produto->percentual_custo = $resultados['percentual_custo'];
            }
        });

        static::updating(function ($produto) {
            if ($produto->isDirty('descricao') && empty($produto->slug)) {
                $baseSlug = Str::slug($produto->descricao);
                $slug = $baseSlug;
                $counter = 1;
                
                while (static::where('slug', $slug)->where('id', '!=', $produto->id)->exists()) {
                    $slug = $baseSlug . '-' . $counter;
                    $counter++;
                }
                $produto->slug = $slug;
            }
            
            if ($produto->isDirty(['quantidade', 'ativo', 'estoque_minimo'])) {
                $stockVO = new Stock(
                    (int) ($produto->quantidade ?? 0),
                    (int) ($produto->estoque_minimo ?? 5)
                );
                $produto->disponibilidade = $stockVO->getDisponibilidade();
            }

            if ($produto->isDirty('quantidade')) {
                $produto->ultima_atualizacao_estoque = now();
            }

            if ($produto->isDirty(['valor_compra', 'margem_lucro', 'ipi'])) {
                $calculator = new PricingCalculator();
                $resultados = $calculator->calculate(
                    (float) $produto->valor_compra,
                    (float) ($produto->margem_lucro ?? 80),
                    (float) ($produto->ipi ?? 0)
                );
                
                $produto->valor_custo = $resultados['valor_custo'];
                $produto->valor_atacado = $resultados['valor_atacado'];
                $produto->percentual_custo = $resultados['percentual_custo'];
            }
        });
    }
}
