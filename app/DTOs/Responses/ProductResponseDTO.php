<?php

namespace App\DTOs\Responses;

use App\Models\Produto;

class ProductResponseDTO
{
    public function __construct(
        public readonly int $id,
        public readonly string $descricao,
        public readonly string $categoria,
        public readonly ?string $referencia,
        public readonly ?string $slug,
        public readonly ?string $tipo,
        public readonly string $disponibilidade,
        public readonly ?string $imagem,
        public readonly int $quantidade,
        public readonly int $estoque_minimo,
        public readonly ?float $valor_atacado,
        public readonly ?float $valor_compra,
        public readonly ?float $valor_unitario,
        public readonly ?float $valor_custo,
        public readonly ?float $preco_promocional,
        public readonly ?float $ipi,
        public readonly ?float $percentual_custo,
        public readonly ?float $margem_lucro,
        public readonly bool $ativo,
        public readonly bool $destaque,
        public readonly bool $novo,
        public readonly bool $mais_vendido,
        public readonly ?string $data_compra,
        public readonly int $visualizacoes,
        public readonly ?string $created_at,
        public readonly ?string $updated_at,
        public readonly ?float $preco_com_ipi,
        public readonly ?string $preco_com_ipi_formatado,
        public readonly ?string $ipi_formatado,
    ) {}

    /**
     * Criar DTO a partir de um Model
     */
    public static function fromModel(Produto $produto): self
    {
        return new self(
            id: $produto->id,
            descricao: $produto->descricao,
            categoria: $produto->categoria,
            referencia: $produto->referencia,
            slug: $produto->slug,
            tipo: $produto->tipo,
            disponibilidade: $produto->disponibilidade,
            imagem: $produto->imagem,
            quantidade: $produto->quantidade,
            estoque_minimo: $produto->estoque_minimo ?? 5,
            valor_atacado: $produto->valor_atacado,
            valor_compra: $produto->valor_compra,
            valor_unitario: $produto->valor_unitario,
            valor_custo: $produto->valor_custo,
            preco_promocional: $produto->preco_promocional,
            ipi: $produto->ipi,
            percentual_custo: $produto->percentual_custo,
            margem_lucro: $produto->margem_lucro,
            ativo: (bool) $produto->ativo,
            destaque: (bool) $produto->destaque,
            novo: (bool) ($produto->novo ?? false),
            mais_vendido: (bool) ($produto->mais_vendido ?? false),
            data_compra: $produto->data_compra,
            visualizacoes: $produto->visualizacoes ?? 0,
            created_at: $produto->created_at?->toDateTimeString(),
            updated_at: $produto->updated_at?->toDateTimeString(),
            preco_com_ipi: $produto->preco_com_ipi ?? 0,
            preco_com_ipi_formatado: $produto->preco_com_ipi_formatado ?? 'R$ 0,00',
            ipi_formatado: $produto->ipi_formatado ?? '0,00%',
        );
    }

    /**
     * Converter para Array
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
            'disponibilidade' => $this->disponibilidade,
            'imagem' => $this->imagem,
            'imagem_url' => $this->getImagemUrl(),
            'quantidade' => $this->quantidade,
            'estoque_minimo' => $this->estoque_minimo,
            'valor_atacado' => $this->valor_atacado,
            'valor_atacado_formatado' => $this->getPrecoAtacadoFormatado(),
            'valor_unitario' => $this->valor_unitario,
            'valor_unitario_formatado' => $this->getPrecoFormatado(),
            'valor_custo' => $this->valor_custo,
            'preco_promocional' => $this->preco_promocional,
            'preco_promocional_formatado' => $this->getPrecoPromocionalFormatado(),
            'ipi' => $this->ipi,
            'ipi_formatado' => $this->ipi_formatado,
            'preco_com_ipi' => $this->preco_com_ipi,
            'preco_com_ipi_formatado' => $this->preco_com_ipi_formatado,
            'tem_ipi' => ($this->ipi ?? 0) > 0,
            'percentual_custo' => $this->percentual_custo,
            'margem_lucro' => $this->margem_lucro,
            'ativo' => $this->ativo,
            'destaque' => $this->destaque,
            'novo' => $this->novo,
            'mais_vendido' => $this->mais_vendido,
            'data_compra' => $this->data_compra,
            'visualizacoes' => $this->visualizacoes,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'created_at_formatado' => $this->getCreatedAtFormatted(),
            'tem_promocao' => $this->temPromocao(),
            'status' => $this->getStatus(),
            'disponivel' => $this->isDisponivel(),
            'status_badge' => $this->getStatusBadge(),
        ];
    }

    // ============================================
    // MÉTODOS AUXILIARES
    // ============================================

    public function getPrecoFormatado(): string
    {
        if ($this->valor_unitario) {
            return 'R$ ' . number_format($this->valor_unitario, 2, ',', '.');
        }
        return 'R$ 0,00';
    }

    public function getPrecoAtacadoFormatado(): string
    {
        if ($this->valor_atacado) {
            return 'R$ ' . number_format($this->valor_atacado, 2, ',', '.');
        }
        return 'R$ 0,00';
    }

    public function getPrecoPromocionalFormatado(): string
    {
        if ($this->preco_promocional) {
            return 'R$ ' . number_format($this->preco_promocional, 2, ',', '.');
        }
        return '';
    }

    public function temPromocao(): bool
    {
        return $this->preco_promocional !== null 
            && $this->preco_promocional > 0 
            && $this->valor_atacado !== null
            && $this->preco_promocional < $this->valor_atacado;
    }

    public function getStatus(): string
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

    public function getStatusBadge(): string
    {
        $status = $this->getStatus();
        return match($status) {
            'Disponível' => 'success',
            'Indisponível' => 'warning',
            'Esgotado' => 'danger',
            'Inativo' => 'secondary',
            default => 'secondary'
        };
    }

    public function isDisponivel(): bool
    {
        return $this->ativo 
            && $this->disponibilidade === 'DISPONÍVEL'
            && $this->quantidade > 0;
    }

    public function getImagemUrl(): string
    {
        if ($this->imagem) {
            return asset('storage/produtos/' . $this->imagem);
        }
        return asset('images/produto-placeholder.jpg');
    }

    public function getCreatedAtFormatted(): string
    {
        if ($this->created_at) {
            return \Carbon\Carbon::parse($this->created_at)->format('d/m/Y H:i');
        }
        return '-';
    }

    public function getUpdatedAtFormatted(): string
    {
        if ($this->updated_at) {
            return \Carbon\Carbon::parse($this->updated_at)->format('d/m/Y H:i');
        }
        return '-';
    }
}