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
            'q' => ['nullable', 'string', 'min:2', 'max:100'],
            'categoria' => ['nullable', 'exists:categorias,id'],
            'ordem' => ['nullable', 'string', 'in:preco_asc,preco_desc,nome,novos,destaque,mais_vendidos'],
            'preco_min' => ['nullable', 'numeric', 'min:0'],
            'preco_max' => ['nullable', 'numeric', 'min:0', 'gte:preco_min'],
            'pagina' => ['nullable', 'integer', 'min:1'],
            'por_pagina' => ['nullable', 'integer', 'min:1', 'max:100'],
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
            'q.min' => 'Digite pelo menos 2 caracteres para buscar.',
            'categoria.exists' => 'Categoria inválida.',
            'ordem.in' => 'Opção de ordenação inválida.',
            'preco_max.gte' => 'O preço máximo deve ser maior que o preço mínimo.',
            'por_pagina.max' => 'O valor não pode ser maior que 100.',
        ];
    }

    /**
     * Get the validated data with defaults.
     *
     * @return array<string, mixed>
     */
    public function validated($key = null, $default = null): array
    {
        return array_merge([
            'q' => null,
            'categoria' => null,
            'ordem' => 'novos',
            'preco_min' => null,
            'preco_max' => null,
            'pagina' => 1,
            'por_pagina' => 12,
        ], parent::validated());
    }
}