<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class ProdutoRequest extends FormRequest
{
    /**
     * Determinar se o usuário está autorizado
     */
    public function authorize(): bool
    {
        return auth()->user()->hasRole('Admin');
    }

    /**
     * Regras de validação
     */
    public function rules(): array
    {
        $rules = [
            'descricao' => 'required|string|max:255',
            'categoria' => 'required|string|max:100',
            'valor_unitario' => 'required|numeric|min:0',
            'preco_promocional' => 'nullable|numeric|min:0|lt:valor_unitario',
            'quantidade' => 'required|integer|min:0',
            'disponibilidade' => 'required|in:DISPONÍVEL,INDISPONÍVEL',
            'referencia' => 'nullable|string|max:50',
            'imagem' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'ativo' => 'boolean'
        ];

        // Na atualização, tornamos a imagem opcional
        if ($this->isMethod('put') || $this->isMethod('patch')) {
            $rules['imagem'] = 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048';
        }

        return $rules;
    }

    /**
     * Mensagens de validação
     */
    public function messages(): array
    {
        return [
            'descricao.required' => 'A descrição é obrigatória.',
            'categoria.required' => 'A categoria é obrigatória.',
            'valor_unitario.required' => 'O valor unitário é obrigatório.',
            'valor_unitario.numeric' => 'O valor unitário deve ser um número.',
            'valor_unitario.min' => 'O valor unitário não pode ser negativo.',
            'preco_promocional.lt' => 'O preço promocional deve ser menor que o valor unitário.',
            'quantidade.required' => 'A quantidade é obrigatória.',
            'quantidade.integer' => 'A quantidade deve ser um número inteiro.',
            'quantidade.min' => 'A quantidade não pode ser negativa.',
            'disponibilidade.required' => 'A disponibilidade é obrigatória.',
            'disponibilidade.in' => 'A disponibilidade deve ser DISPONÍVEL ou INDISPONÍVEL.',
            'imagem.image' => 'O arquivo deve ser uma imagem.',
            'imagem.mimes' => 'A imagem deve ser do tipo: jpeg, png, jpg, gif.',
            'imagem.max' => 'A imagem não pode ter mais que 2MB.',
        ];
    }

    /**
     * Preparar os dados antes da validação
     */
    protected function prepareForValidation(): void
    {
        // Garantir que preco_promocional seja null se vazio
        if (empty($this->preco_promocional)) {
            $this->merge(['preco_promocional' => null]);
        }

        // Garantir que ativo seja boolean
        if ($this->has('ativo')) {
            $this->merge(['ativo' => filter_var($this->ativo, FILTER_VALIDATE_BOOLEAN)]);
        }
    }
}