<?php

namespace App\Http\Requests\Carrinho;

use Illuminate\Foundation\Http\FormRequest;

class AdicionarRequest extends FormRequest
{
    // Adicione esta constante local
    private const MAX_QUANTITY_PER_ITEM = 999;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'produto_id' => ['required', 'exists:produtos,id'],
            'quantidade' => ['required', 'integer', 'min:1', 'max:' . self::MAX_QUANTITY_PER_ITEM],
        ];
    }

    public function messages(): array
    {
        return [
            'produto_id.required' => 'Selecione um produto.',
            'produto_id.exists' => 'Produto inválido.',
            'quantidade.required' => 'Informe a quantidade.',
            'quantidade.integer' => 'A quantidade deve ser um número inteiro.',
            'quantidade.min' => 'A quantidade deve ser pelo menos 1.',
            'quantidade.max' => "A quantidade máxima por item é " . self::MAX_QUANTITY_PER_ITEM . ".",
        ];
    }
}