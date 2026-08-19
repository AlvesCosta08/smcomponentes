<?php
// app/Http/Requests/CheckoutRequest.php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CheckoutRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'endereco' => 'required|string|min:5|max:255',
            'numero' => 'nullable|string|max:10',
            'complemento' => 'nullable|string|max:100',
            'bairro' => 'nullable|string|max:100',
            'cidade' => 'required|string|min:3|max:100',
            'estado' => 'required|string|size:2',
            'cep' => 'required|string|size:8',
            'telefone' => 'nullable|string|max:20',
            'forma_pagamento' => 'required|in:cartao,boleto,pix',
            'observacoes' => 'nullable|string|max:500',
            
            // Dados do cartão (se for cartão)
            'numero_cartao' => 'required_if:forma_pagamento,cartao|string|size:16',
            'validade_cartao' => 'required_if:forma_pagamento,cartao|string|size:7',
            'cvv_cartao' => 'required_if:forma_pagamento,cartao|string|size:3',
            'nome_cartao' => 'required_if:forma_pagamento,cartao|string|max:100',
            'parcelas' => 'required_if:forma_pagamento,cartao|integer|min:1|max:12',
        ];
    }

    public function messages(): array
    {
        return [
            'endereco.required' => 'O endereço é obrigatório.',
            'cidade.required' => 'A cidade é obrigatória.',
            'estado.required' => 'O estado é obrigatório.',
            'estado.size' => 'O estado deve ter 2 caracteres (UF).',
            'cep.required' => 'O CEP é obrigatório.',
            'cep.size' => 'O CEP deve ter 8 dígitos.',
            'forma_pagamento.required' => 'Selecione uma forma de pagamento.',
            'forma_pagamento.in' => 'Forma de pagamento inválida.',
            
            // Validações do cartão
            'numero_cartao.required_if' => 'O número do cartão é obrigatório para pagamento com cartão.',
            'numero_cartao.size' => 'O número do cartão deve ter 16 dígitos.',
            'validade_cartao.required_if' => 'A validade do cartão é obrigatória.',
            'validade_cartao.size' => 'A validade deve estar no formato MM/AAAA.',
            'cvv_cartao.required_if' => 'O CVV do cartão é obrigatório.',
            'cvv_cartao.size' => 'O CVV deve ter 3 dígitos.',
            'nome_cartao.required_if' => 'O nome no cartão é obrigatório.',
            'parcelas.required_if' => 'O número de parcelas é obrigatório.',
        ];
    }

    /**
     * Preparar dados para validação
     */
    protected function prepareForValidation(): void
    {
        // Remover máscaras
        if ($this->has('cep')) {
            $this->merge([
                'cep' => preg_replace('/[^0-9]/', '', $this->cep)
            ]);
        }

        if ($this->has('numero_cartao')) {
            $this->merge([
                'numero_cartao' => preg_replace('/[^0-9]/', '', $this->numero_cartao)
            ]);
        }

        if ($this->has('cvv_cartao')) {
            $this->merge([
                'cvv_cartao' => preg_replace('/[^0-9]/', '', $this->cvv_cartao)
            ]);
        }
    }
}