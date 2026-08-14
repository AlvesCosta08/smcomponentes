<?php
// app/Http/Requests/WishlistRequest.php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class WishlistRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'nome' => 'required|string|max:100',
            'is_default' => 'nullable|boolean',
            'is_public' => 'nullable|boolean',
            'descricao' => 'nullable|string|max:500',
        ];
    }

    public function messages(): array
    {
        return [
            'nome.required' => 'O nome da lista é obrigatório.',
            'nome.max' => 'O nome não pode ter mais que 100 caracteres.',
            'descricao.max' => 'A descrição não pode ter mais que 500 caracteres.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_default' => filter_var($this->is_default ?? false, FILTER_VALIDATE_BOOLEAN),
            'is_public' => filter_var($this->is_public ?? false, FILTER_VALIDATE_BOOLEAN),
        ]);
    }
}