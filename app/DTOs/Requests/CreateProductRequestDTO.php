<?php

namespace App\DTOs\Requests;

use App\DTOs\ProductDTO;
use Illuminate\Http\Request;

class CreateProductRequestDTO extends ProductDTO
{
    public static function fromRequest(Request $request): self
    {
        return new self(
            id: null,
            descricao: $request->input('descricao'),
            categoria: $request->input('categoria'),
            referencia: $request->input('referencia'),
            slug: $request->input('slug') ?? \Illuminate\Support\Str::slug($request->input('descricao')),
            tipo: $request->input('tipo'),
            disponibilidade: $request->input('disponibilidade', 'DISPONÍVEL'),
            imagem: null,
            imagem_file: $request->file('imagem'),
            quantidade: (int) $request->input('quantidade', 0),
            estoque_minimo: (int) $request->input('estoque_minimo', 5),
            valor_atacado: $request->has('valor_atacado') ? (float) $request->input('valor_atacado') : null,
            valor_compra: $request->has('valor_compra') ? (float) $request->input('valor_compra') : null,
            valor_unitario: $request->has('valor_unitario') ? (float) $request->input('valor_unitario') : null,
            valor_custo: $request->has('valor_custo') ? (float) $request->input('valor_custo') : null,
            preco_promocional: $request->has('preco_promocional') ? (float) $request->input('preco_promocional') : null,
            ipi: $request->has('ipi') ? (float) $request->input('ipi') : null,
            percentual_custo: $request->has('percentual_custo') ? (float) $request->input('percentual_custo') : null,
            margem_lucro: $request->has('margem_lucro') ? (float) $request->input('margem_lucro') : null,
            ativo: filter_var($request->input('ativo', true), FILTER_VALIDATE_BOOLEAN),
            destaque: filter_var($request->input('destaque', false), FILTER_VALIDATE_BOOLEAN),
            data_compra: $request->input('data_compra'),
            visualizacoes: 0,
            novo: filter_var($request->input('novo', false), FILTER_VALIDATE_BOOLEAN),
            mais_vendido: filter_var($request->input('mais_vendido', false), FILTER_VALIDATE_BOOLEAN),
        );
    }
}