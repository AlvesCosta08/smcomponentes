<?php
// app/DTOs/Responses/ProductResponseDTO.php

namespace App\DTOs\Responses;

use App\DTOs\ProductDTO;
use App\Models\Produto;

class ProductResponseDTO extends ProductDTO
{
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
            imagem_file: null,
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
            data_compra: $produto->data_compra,
            visualizacoes: $produto->visualizacoes ?? 0,
            novo: $produto->novo ?? false,
            mais_vendido: $produto->mais_vendido ?? false,
        );
    }

    /**
     * Para API - Retorna apenas dados relevantes para o cliente
     */
    public function toApiResponse(): array
    {
        return [
            'id' => $this->id,
            'descricao' => $this->descricao,
            'slug' => $this->slug,
            'referencia' => $this->referencia,
            'categoria' => $this->categoria,
            'preco' => $this->getPrecoFormatado(),
            'preco_promocional' => $this->getPrecoPromocionalFormatado(),
            'tem_promocao' => $this->temPromocao(),
            'estoque' => $this->quantidade,
            'status' => $this->getStatus(),
            'disponivel' => $this->isDisponivel(),
            'imagem_url' => $this->getImagemUrl(),
            'destaque' => $this->destaque,
            'novo' => $this->novo,
            'mais_vendido' => $this->mais_vendido,
        ];
    }
}