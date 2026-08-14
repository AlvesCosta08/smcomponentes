<?php
// app/Http/Requests/Produto/BuscarProdutoRequest.php

namespace App\Http\Requests\Produto;

use Illuminate\Foundation\Http\FormRequest;

class BuscarProdutoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'q' => 'required|string|min:2|max:100',
        ];
    }

    public function messages(): array
    {
        return [
            'q.required' => 'Digite um termo para buscar.',
            'q.min' => 'Digite pelo menos 2 caracteres para buscar.',
            'q.max' => 'O termo de busca não pode ter mais que 100 caracteres.',
        ];
    }
}