<?php

namespace App\Http\Requests\Produto;

use Illuminate\Foundation\Http\FormRequest;

class BuscarProdutoRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'q'           => ['nullable', 'string', 'min:2', 'max:100'],
            'categoria'   => ['nullable', 'integer', 'exists:categorias,id'],
            'status'      => ['nullable', 'in:disponivel,indisponivel,estoque_baixo,sob_encomenda'],
            'order'       => ['nullable', 'in:created_at,valor_atacado,valor_unitario,descricao,referencia,visualizacoes'],
            'dir'         => ['nullable', 'in:asc,desc'],
            'ordem'       => ['nullable', 'string', 'in:preco_asc,preco_desc,nome,novos,destaque,mais_vendidos'],
            'preco_min'   => ['nullable', 'numeric', 'min:0'],
            'preco_max'   => ['nullable', 'numeric', 'min:0', 'gte:preco_min'],
            'page'        => ['nullable', 'integer', 'min:1'],
            'pagina'      => ['nullable', 'integer', 'min:1'],
            'per_page'    => ['nullable', 'integer', 'min:1', 'max:100'],
            'por_pagina'  => ['nullable', 'integer', 'min:1', 'max:100'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'q.min'              => 'Digite pelo menos 2 caracteres para buscar.',
            'q.max'              => 'A busca não pode ter mais de 100 caracteres.',
            'categoria.integer'  => 'Categoria inválida.',
            'categoria.exists'   => 'Categoria não encontrada.',
            'status.in'          => 'Status inválido.',
            'order.in'           => 'Opção de ordenação inválida.',
            'dir.in'             => 'Direção de ordenação inválida.',
            'ordem.in'           => 'Opção de ordenação inválida.',
            'preco_min.numeric'  => 'O preço mínimo deve ser um número.',
            'preco_min.min'      => 'O preço mínimo não pode ser negativo.',
            'preco_max.numeric'  => 'O preço máximo deve ser um número.',
            'preco_max.gte'      => 'O preço máximo deve ser maior ou igual ao preço mínimo.',
            'per_page.max'       => 'O valor não pode ser maior que 100.',
            'por_pagina.max'     => 'O valor não pode ser maior que 100.',
        ];
    }

    /**
     * Get the search term.
     */
    public function getTermo(): string
    {
        return trim((string) $this->input('q', ''));
    }

    /**
     * Get items per page.
     */
    public function getPorPagina(): int
    {
        return (int) ($this->input('por_pagina')
            ?? $this->input('per_page')
            ?? 12);
    }

    /**
     * Get order field.
     */
    public function getOrder(): string
    {
        return (string) $this->input('order', 'created_at');
    }

    /**
     * Get order direction.
     */
    public function getDir(): string
    {
        $dir = (string) $this->input('dir', 'desc');
        return in_array($dir, ['asc', 'desc'], true) ? $dir : 'desc';
    }
}