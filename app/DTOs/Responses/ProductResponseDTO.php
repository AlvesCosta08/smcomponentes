<?php
// app/DTOs/Responses/ProductResponseDTO.php

namespace App\DTOs\Responses;

use App\Models\Produto;
use Illuminate\Support\Facades\Storage;

class ProductResponseDTO
{
    public function __construct(
        // Dados básicos
        public int $id,
        public string $descricao,
        public ?string $categoria,
        public ?string $referencia,
        public ?string $slug,
        public ?string $tipo,
        
        // Estoque
        public int $quantidade,
        public int $estoque_minimo,
        public string $disponibilidade,
        
        // ✅ PREÇOS - valor_atacado é o principal
        public ?float $valor_compra,
        public ?float $valor_custo,
        public ?float $valor_atacado,      // ✅ Preço principal
        public ?float $valor_unitario,     // Preço secundário
        public ?float $preco_promocional,
        
        // ✅ IPI e Margem
        public ?float $ipi,
        public ?float $margem_lucro,
        public ?float $percentual_custo,
        
        // ✅ CAMPOS CALCULADOS (appends)
        public ?string $preco_formatado,
        public ?string $preco_atacado_formatado,
        public ?string $preco_promocional_formatado,
        public ?float $preco_com_ipi,
        public ?string $preco_com_ipi_formatado,
        public ?float $valor_ipi,
        public ?string $valor_ipi_formatado,
        public ?string $ipi_aliquota,
        public ?bool $possui_ipi,
        public ?bool $tem_promocao,
        public ?int $desconto_percentual,
        public ?string $status_label,
        public ?bool $disponivel,
        
        // Status
        public bool $ativo,
        public bool $destaque,
        public bool $novo,
        public bool $mais_vendido,
        
        // Imagens
        public ?string $imagem,
        public ?string $imagem_url,
        public ?array $galeria_urls,
        
        // Datas
        public ?string $data_compra,
        public ?string $created_at,
        public ?string $updated_at,
        
        // Métricas
        public ?int $visualizacoes,
    ) {}

    /**
     * Criar DTO a partir do modelo Produto
     */
    public static function fromModel(Produto $produto): self
    {
        // Processa galeria
        $galeriaUrls = [];
        if ($produto->galeria) {
            $galeria = json_decode($produto->galeria, true);
            if (is_array($galeria)) {
                foreach ($galeria as $path) {
                    if ($path && Storage::disk('public')->exists($path)) {
                        $galeriaUrls[] = Storage::disk('public')->url($path);
                    }
                }
            }
        }

        // Processa imagem principal
        $imagemUrl = null;
        if ($produto->imagem && Storage::disk('public')->exists($produto->imagem)) {
            $imagemUrl = Storage::disk('public')->url($produto->imagem);
        }

        // ✅ Calcula o preço com IPI
        $precoComIPI = null;
        $valorIPI = null;
        if ($produto->valor_atacado && $produto->ipi) {
            $precoComIPI = round($produto->valor_atacado * (1 + ($produto->ipi / 100)), 2);
            $valorIPI = round($produto->valor_atacado * ($produto->ipi / 100), 2);
        }

        // ✅ Verifica se tem promoção
        $temPromocao = $produto->preco_promocional && 
                       $produto->preco_promocional > 0 && 
                       $produto->preco_promocional < $produto->valor_atacado;

        // ✅ Calcula desconto percentual
        $descontoPercentual = 0;
        if ($temPromocao && $produto->valor_atacado > 0) {
            $descontoPercentual = (int) round((($produto->valor_atacado - $produto->preco_promocional) / $produto->valor_atacado) * 100);
        }

        return new self(
            id: $produto->id,
            descricao: $produto->descricao,
            categoria: $produto->categoria,
            referencia: $produto->referencia,
            slug: $produto->slug,
            tipo: $produto->tipo,
            quantidade: $produto->quantidade,
            estoque_minimo: $produto->estoque_minimo,
            disponibilidade: $produto->disponibilidade,
            
            // ✅ Preços
            valor_compra: $produto->valor_compra,
            valor_custo: $produto->valor_custo,
            valor_atacado: $produto->valor_atacado,
            valor_unitario: $produto->valor_unitario,
            preco_promocional: $produto->preco_promocional,
            
            // ✅ IPI e Margem
            ipi: $produto->ipi,
            margem_lucro: $produto->margem_lucro,
            percentual_custo: $produto->percentual_custo,
            
            // ✅ Campos calculados
            preco_formatado: 'R$ ' . number_format($produto->valor_atacado ?? 0, 2, ',', '.'),
            preco_atacado_formatado: 'R$ ' . number_format($produto->valor_atacado ?? 0, 2, ',', '.'),
            preco_promocional_formatado: $produto->preco_promocional ? 'R$ ' . number_format($produto->preco_promocional, 2, ',', '.') : null,
            preco_com_ipi: $precoComIPI,
            preco_com_ipi_formatado: $precoComIPI ? 'R$ ' . number_format($precoComIPI, 2, ',', '.') : null,
            valor_ipi: $valorIPI,
            valor_ipi_formatado: $valorIPI ? 'R$ ' . number_format($valorIPI, 2, ',', '.') : null,
            ipi_aliquota: $produto->ipi ? number_format($produto->ipi, 2, ',', '.') . '%' : null,
            possui_ipi: ($produto->ipi ?? 0) > 0,
            tem_promocao: $temPromocao,
            desconto_percentual: $descontoPercentual,
            status_label: $produto->getStatusLabelAttribute(),
            disponivel: $produto->disponivel,
            
            // Status
            ativo: (bool) $produto->ativo,
            destaque: (bool) $produto->destaque,
            novo: (bool) ($produto->novo ?? false),
            mais_vendido: (bool) ($produto->mais_vendido ?? false),
            
            // Imagens
            imagem: $produto->imagem,
            imagem_url: $imagemUrl,
            galeria_urls: $galeriaUrls,
            
            // Datas
            data_compra: $produto->data_compra?->toDateString(),
            created_at: $produto->created_at?->toISOString(),
            updated_at: $produto->updated_at?->toISOString(),
            
            // Métricas
            visualizacoes: $produto->visualizacoes ?? 0,
        );
    }

    /**
     * Converter para array
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'descricao' => $this->descricao,
            'categoria' => $this->categoria,
            'referencia' => $this->referencia,
            'slug' => $this->slug,
            'tipo' => $this->tipo,
            'quantidade' => $this->quantidade,
            'estoque_minimo' => $this->estoque_minimo,
            'disponibilidade' => $this->disponibilidade,
            
            // ✅ Preços
            'valor_compra' => $this->valor_compra,
            'valor_custo' => $this->valor_custo,
            'valor_atacado' => $this->valor_atacado,
            'valor_unitario' => $this->valor_unitario,
            'preco_promocional' => $this->preco_promocional,
            
            // ✅ IPI e Margem
            'ipi' => $this->ipi,
            'margem_lucro' => $this->margem_lucro,
            'percentual_custo' => $this->percentual_custo,
            
            // ✅ Formatados
            'preco_formatado' => $this->preco_formatado,
            'preco_atacado_formatado' => $this->preco_atacado_formatado,
            'preco_promocional_formatado' => $this->preco_promocional_formatado,
            'preco_com_ipi' => $this->preco_com_ipi,
            'preco_com_ipi_formatado' => $this->preco_com_ipi_formatado,
            'valor_ipi' => $this->valor_ipi,
            'valor_ipi_formatado' => $this->valor_ipi_formatado,
            'ipi_aliquota' => $this->ipi_aliquota,
            'possui_ipi' => $this->possui_ipi,
            'tem_promocao' => $this->tem_promocao,
            'desconto_percentual' => $this->desconto_percentual,
            'status_label' => $this->status_label,
            'disponivel' => $this->disponivel,
            
            // Status
            'ativo' => $this->ativo,
            'destaque' => $this->destaque,
            'novo' => $this->novo,
            'mais_vendido' => $this->mais_vendido,
            
            // Imagens
            'imagem' => $this->imagem,
            'imagem_url' => $this->imagem_url,
            'galeria_urls' => $this->galeria_urls,
            
            // Datas
            'data_compra' => $this->data_compra,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            
            // Métricas
            'visualizacoes' => $this->visualizacoes,
        ];
    }
}