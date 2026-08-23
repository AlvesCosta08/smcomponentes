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
    // ACESSORS (GETTERS) - CORRIGIDOS
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

    /**
     * CORRIGIDO: Obtém URL da imagem via rota do ImageController
     */
    public function getImagemUrlAttribute(): string
    {
        // Se tem imagem no campo
        if ($this->imagem) {
            $filename = basename($this->imagem);
            
            // Verifica se existe no storage
            $paths = [
                'produtos/' . $filename,
                'uploads/produtos/' . $filename,
                'images/produtos/' . $filename,
            ];
            
            foreach ($paths as $path) {
                if (Storage::disk('public')->exists($path)) {
                    return route('image.show', ['filename' => $filename]);
                }
            }
            
            // Se não encontrou, tenta sem a pasta
            if (Storage::disk('public')->exists($this->imagem)) {
                return route('image.show', ['filename' => basename($this->imagem)]);
            }
        }

        // Tenta imagens relacionadas
        if ($this->relationLoaded('imagens') && $this->imagens->isNotEmpty()) {
            foreach ($this->imagens as $imagem) {
                $filename = basename($imagem->imagem);
                $path = 'produtos/' . $filename;
                if (Storage::disk('public')->exists($path)) {
                    return route('image.show', ['filename' => $filename]);
                }
            }
        }

        // Fallback: imagem padrão via controller
        return route('image.show', ['filename' => 'placeholder.png']);
    }

    /**
     * CORRIGIDO: Obtém URL da imagem otimizada
     */
    public function getImageOptimizedUrl(int $width = 400, int $height = 400): string
    {
        if ($this->imagem) {
            $filename = basename($this->imagem);
            $paths = [
                'produtos/' . $filename,
                'uploads/produtos/' . $filename,
            ];
            
            foreach ($paths as $path) {
                if (Storage::disk('public')->exists($path)) {
                    return route('image.optimized', [
                        'filename' => $filename,
                        'width' => $width,
                        'height' => $height
                    ]);
                }
            }
        }

        return route('image.show', ['filename' => 'placeholder.png']);
    }

    public function getImagensUrlsAttribute(): array
    {
        $urls = [];

        if ($this->relationLoaded('imagens') && $this->imagens->isNotEmpty()) {
            foreach ($this->imagens as $imagem) {
                $filename = basename($imagem->imagem);
                $path = 'produtos/' . $filename;
                if (Storage::disk('public')->exists($path)) {
                    $urls[] = route('image.show', ['filename' => $filename]);
                }
            }
        }

        // Fallback para uma única imagem
        if (empty($urls) && $this->imagem) {
            $urls[] = $this->imagem_url;
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
        if ($this->ativo && ($this->quantidade ?? 0) > 0) {
            return true;
        }
        
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

    private function calcularTodosPrecos(array $data): array
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
}