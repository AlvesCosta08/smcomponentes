<?php

namespace App\Models;

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

    // ==============================================
    // ATRIBUTOS APENDADOS
    // ==============================================
    
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
        return $this->getImagemUrl();
    }

    public function getImagensUrlsAttribute(): array
    {
        return $this->getImagensUrls();
    }

    public function getStatusLabelAttribute(): string
    {
        return $this->getStatusLabel();
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

    // ==============================================
    // MÉTODOS AUXILIARES
    // ==============================================

    public function getPrecoFormatado(): string
    {
        return 'R$ ' . number_format($this->valor_atacado ?? 0, 2, ',', '.');
    }

    public function getPrecoAtacadoFormatado(): string
    {
        return 'R$ ' . number_format($this->valor_atacado ?? 0, 2, ',', '.');
    }

    public function getPrecoPromocionalFormatado(): ?string
    {
        return $this->preco_promocional 
            ? 'R$ ' . number_format($this->preco_promocional, 2, ',', '.') 
            : null;
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

    public function getValorIpi(): float
    {
        $preco = $this->valor_atacado ?? 0;
        $ipi = $this->ipi ?? 0;
        return round($preco * ($ipi / 100), 2);
    }

    public function getValorIpiFormatado(): string
    {
        return 'R$ ' . number_format($this->getValorIpi(), 2, ',', '.');
    }

    public function getIpiAliquota(): string
    {
        return number_format($this->ipi ?? 0, 2, ',', '.') . '%';
    }

    public function hasIpi(): bool
    {
        return ($this->ipi ?? 0) > 0;
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

    public function getStatusLabel(): string
    {
        if (!$this->ativo) {
            return 'Inativo';
        }
        return $this->disponibilidade?->label() ?? 'Desconhecido';
    }

    public function isDisponivel(): bool
    {
        return $this->ativo 
            && ($this->quantidade ?? 0) > 0 
            && $this->disponibilidade === DisponibilidadeEnum::DISPONIVEL;
    }

    public function getImagemUrl(): string
    {
        // Primeiro tenta pegar a imagem principal do relacionamento
        if ($this->relationLoaded('imagemPrincipal') && $this->imagemPrincipal) {
            return $this->getImagemPath($this->imagemPrincipal->imagem);
        }

        // Depois tenta a primeira imagem do relacionamento
        if ($this->relationLoaded('imagens') && $this->imagens->isNotEmpty()) {
            return $this->getImagemPath($this->imagens->first()->imagem);
        }

        // Fallback para o campo imagem antigo
        if ($this->imagem) {
            return $this->getImagemPath($this->imagem);
        }

        return asset('images/produto-placeholder.jpg');
    }

    public function getImagensUrls(): array
    {
        if ($this->relationLoaded('imagens') && $this->imagens->isNotEmpty()) {
            return $this->imagens->map(function ($imagem) {
                return $this->getImagemPath($imagem->imagem);
            })->toArray();
        }

        // Fallback para uma única imagem
        return [$this->getImagemUrl()];
    }

    private function getImagemPath(string $path): string
    {
        if (filter_var($path, FILTER_VALIDATE_URL)) {
            return $path;
        }

        if (Storage::disk('public')->exists($path)) {
            return Storage::disk('public')->url($path);
        }

        return asset('images/produto-placeholder.jpg');
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

    public function getMarkup(): float
    {
        $margem = $this->margem_lucro ?? 80;
        if ($margem <= 0 || $margem >= 100) {
            return 1;
        }
        return round(1 / (1 - ($margem / 100)), 2);
    }

    // ==============================================
    // MÉTODOS DE CÁLCULO
    // ==============================================

    public function calcularTodosPrecos(array $data): array
    {
        $valorCompra = (float) ($data['valor_compra'] ?? 0);
        $margem = (float) ($data['margem_lucro'] ?? 80);
        $ipi = (float) ($data['ipi'] ?? 0);

        $custo = round($valorCompra, 2);
        
        $precoAtacado = 0;
        if ($margem > 0 && $margem < 100) {
            $precoAtacado = round($custo / (1 - ($margem / 100)), 2);
        } else {
            $precoAtacado = $custo;
        }

        $percentualCusto = 0;
        if ($precoAtacado > 0) {
            $percentualCusto = round(($custo / $precoAtacado) * 100, 2);
        }

        return [
            'valor_custo' => $custo,
            'valor_atacado' => $precoAtacado,
            'percentual_custo' => $percentualCusto,
        ];
    }

    public function setValorCompraAttribute(?float $value): void
    {
        $this->attributes['valor_compra'] = $value !== null ? round($value, 2) : null;
        $this->recalcularPrecos();
    }

    public function setMargemLucroAttribute(?float $value): void
    {
        $this->attributes['margem_lucro'] = $value !== null ? round($value, 2) : null;
        $this->recalcularPrecos();
    }

    public function setIpiAttribute(?float $value): void
    {
        $this->attributes['ipi'] = $value !== null ? round($value, 2) : null;
        $this->recalcularPrecos();
    }

    private function recalcularPrecos(): void
    {
        $valorCompra = $this->attributes['valor_compra'] ?? 0;
        $margem = $this->attributes['margem_lucro'] ?? 80;
        $ipi = $this->attributes['ipi'] ?? 0;

        if ($valorCompra > 0) {
            $resultados = $this->calcularTodosPrecos([
                'valor_compra' => $valorCompra,
                'margem_lucro' => $margem,
                'ipi' => $ipi,
            ]);

            $this->attributes['valor_custo'] = $resultados['valor_custo'];
            $this->attributes['valor_atacado'] = $resultados['valor_atacado'];
            $this->attributes['percentual_custo'] = $resultados['percentual_custo'];
        } else {
            $this->attributes['valor_custo'] = null;
            $this->attributes['valor_atacado'] = null;
            $this->attributes['percentual_custo'] = null;
        }
    }

    public function atualizarDisponibilidade(): void
    {
        if (!$this->ativo) {
            $this->disponibilidade = DisponibilidadeEnum::INDISPONIVEL;
        } elseif (($this->quantidade ?? 0) <= 0) {
            $this->disponibilidade = DisponibilidadeEnum::INDISPONIVEL;
        } elseif (($this->quantidade ?? 0) <= ($this->estoque_minimo ?? 5)) {
            $this->disponibilidade = DisponibilidadeEnum::ESTOQUE_BAIXO;
        } else {
            $this->disponibilidade = DisponibilidadeEnum::DISPONIVEL;
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
                $resultados = $produto->calcularTodosPrecos([
                    'valor_compra' => $produto->valor_compra,
                    'margem_lucro' => $produto->margem_lucro ?? 80,
                    'ipi' => $produto->ipi ?? 0,
                ]);
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
            
            if ($produto->isDirty(['quantidade', 'ativo'])) {
                $produto->atualizarDisponibilidade();
            }

            if ($produto->isDirty('quantidade')) {
                $produto->ultima_atualizacao_estoque = now();
            }

            if ($produto->isDirty(['valor_compra', 'margem_lucro', 'ipi'])) {
                $resultados = $produto->calcularTodosPrecos([
                    'valor_compra' => $produto->valor_compra,
                    'margem_lucro' => $produto->margem_lucro ?? 80,
                    'ipi' => $produto->ipi ?? 0,
                ]);
                $produto->valor_custo = $resultados['valor_custo'];
                $produto->valor_atacado = $resultados['valor_atacado'];
                $produto->percentual_custo = $resultados['percentual_custo'];
            }
        });
    }
}