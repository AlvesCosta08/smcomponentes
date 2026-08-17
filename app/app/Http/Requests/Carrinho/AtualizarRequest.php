<?php

namespace App\Http\Requests\Carrinho;

use Illuminate\Foundation\Http\FormRequest;

class AtualizarRequest extends FormRequest
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
            'quantidade' => ['required', 'integer', 'min:1', 'max:' . \App\Http\Controllers\CarrinhoController::MAX_QUANTITY_PER_ITEM],
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
            'quantidade.required' => 'Informe a quantidade.',
            'quantidade.integer' => 'A quantidade deve ser um número inteiro.',
            'quantidade.min' => 'A quantidade deve ser pelo menos 1.',
            'quantidade.max' => "A quantidade máxima por item é " . \App\Http\Controllers\CarrinhoController::MAX_QUANTITY_PER_ITEM . ".",
        ];
    }
}