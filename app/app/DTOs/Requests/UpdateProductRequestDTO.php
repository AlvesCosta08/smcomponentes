<?php
// app/DTOs/Requests/UpdateProductRequestDTO.php

namespace App\DTOs\Requests;

use App\DTOs\ProductDTO;
use App\Models\Produto;
use Illuminate\Http\Request;

class UpdateProductRequestDTO extends ProductDTO
{
    public static function fromRequest(Request $request, Produto $produto): self
    {
        return new self(
            id: $produto->id,
            descricao: $request->input('descricao', $produto->descricao),
            categoria: $request->input('categoria', $produto->categoria),
            referencia: $request->input('referencia', $produto->referencia),
            slug: $request->input('slug') ?? \Illuminate\Support\Str::slug($request->input('descricao', $produto->descricao)),
            tipo: $request->input('tipo', $produto->tipo),
            disponibilidade: $request->input('disponibilidade', $produto->disponibilidade),
            imagem: $produto->imagem,
            imagem_file: $request->file('imagem'),
            quantidade: (int) $request->input('quantidade', $produto->quantidade),
            estoque_minimo: (int) $request->input('estoque_minimo', $produto->estoque_minimo ?? 5),
            valor_atacado: $request->has('valor_atacado') ? (float) $request->input('valor_atacado') : $produto->valor_atacado,
            valor_compra: $request->has('valor_compra') ? (float) $request->input('valor_compra') : $produto->valor_compra,
            valor_unitario: $request->has('valor_unitario') ? (float) $request->input('valor_unitario') : $produto->valor_unitario,
            valor_custo: $request->has('valor_custo') ? (float) $request->input('valor_custo') : $produto->valor_custo,
            preco_promocional: $request->has('preco_promocional') ? (float) $request->input('preco_promocional') : $produto->preco_promocional,
            ipi: $request->has('ipi') ? (float) $request->input('ipi') : $produto->ipi,
            percentual_custo: $request->has('percentual_custo') ? (float) $request->input('percentual_custo') : $produto->percentual_custo,
            margem_lucro: $request->has('margem_lucro') ? (float) $request->input('margem_lucro') : $produto->margem_lucro,
            ativo: $request->has('ativo') ? filter_var($request->input('ativo'), FILTER_VALIDATE_BOOLEAN) : (bool) $produto->ativo,
            destaque: $request->has('destaque') ? filter_var($request->input('destaque'), FILTER_VALIDATE_BOOLEAN) : (bool) $produto->destaque,
            data_compra: $request->input('data_compra', $produto->data_compra),
            visualizacoes: $produto->visualizacoes ?? 0,
            novo: $request->has('novo') ? filter_var($request->input('novo'), FILTER_VALIDATE_BOOLEAN) : ($produto->novo ?? false),
            mais_vendido: $request->has('mais_vendido') ? filter_var($request->input('mais_vendido'), FILTER_VALIDATE_BOOLEAN) : ($produto->mais_vendido ?? false),
        );
    }
}