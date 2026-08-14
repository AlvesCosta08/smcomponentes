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
            'referencia' => 'nullable|string|max:50',
            'valor_atacado' => 'required|numeric|min:0',
            'valor_unitario' => 'nullable|numeric|min:0',
            'valor_compra' => 'nullable|numeric|min:0',
            'valor_custo' => 'nullable|numeric|min:0',
            'preco_promocional' => 'nullable|numeric|min:0|lt:valor_atacado',
            'ipi' => 'nullable|numeric|min:0|max:100',
            'quantidade' => 'required|integer|min:0',
            'estoque_minimo' => 'nullable|integer|min:0',
            'disponibilidade' => 'required|in:DISPONÍVEL,INDISPONÍVEL',
            'tipo' => 'nullable|string|max:50',
            'data_compra' => 'nullable|date',
            'percentual_custo' => 'nullable|numeric|min:0',
            'margem_lucro' => 'nullable|numeric|min:0',
            'imagem' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'ativo' => 'boolean',
            'destaque' => 'boolean',
            'novo' => 'boolean',
            'mais_vendido' => 'boolean',
        ];

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
            'valor_atacado.required' => 'O preço de atacado é obrigatório.',
            'valor_atacado.numeric' => 'O preço de atacado deve ser um número.',
            'valor_atacado.min' => 'O preço de atacado não pode ser negativo.',
            'preco_promocional.lt' => 'O preço promocional deve ser menor que o preço de atacado.',
            'ipi.numeric' => 'O IPI deve ser um número.',
            'ipi.min' => 'O IPI não pode ser negativo.',
            'ipi.max' => 'O IPI não pode ser maior que 100%.',
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
        if (empty($this->preco_promocional)) {
            $this->merge(['preco_promocional' => null]);
        }

        if (empty($this->ipi) && $this->ipi !== '0') {
            $this->merge(['ipi' => 9.75]);
        }

        if ($this->has('ativo')) {
            $this->merge(['ativo' => filter_var($this->ativo, FILTER_VALIDATE_BOOLEAN)]);
        }

        if ($this->has('destaque')) {
            $this->merge(['destaque' => filter_var($this->destaque, FILTER_VALIDATE_BOOLEAN)]);
        }

        if ($this->has('novo')) {
            $this->merge(['novo' => filter_var($this->novo, FILTER_VALIDATE_BOOLEAN)]);
        }

        if ($this->has('mais_vendido')) {
            $this->merge(['mais_vendido' => filter_var($this->mais_vendido, FILTER_VALIDATE_BOOLEAN)]);
        }
    }
}