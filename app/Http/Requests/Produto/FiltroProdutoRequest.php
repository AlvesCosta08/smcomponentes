<?php
// app/Http/Requests/Produto/FiltroProdutoRequest.php

namespace App\Http\Requests\Produto;

use Illuminate\Foundation\Http\FormRequest;

class FiltroProdutoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Público
    }

    public function rules(): array
    {
        return [
            'categoria' => 'nullable|string|max:100',
            'preco_min' => 'nullable|numeric|min:0',
            'preco_max' => 'nullable|numeric|min:0|gt:preco_min',
            'disponibilidade' => 'nullable|in:DISPONIVEL,EST.BAIXO,INDISPONIVEL',
            'order' => 'nullable|in:created_at,valor_unitario,descricao,categoria,quantidade',
            'dir' => 'nullable|in:asc,desc',
            'page' => 'nullable|integer|min:1',
        ];
    }

    public function messages(): array
    {
        return [
            'preco_min.numeric' => 'O preço mínimo deve ser um número válido.',
            'preco_max.numeric' => 'O preço máximo deve ser um número válido.',
            'preco_max.gt' => 'O preço máximo deve ser maior que o preço mínimo.',
        ];
    }
}
