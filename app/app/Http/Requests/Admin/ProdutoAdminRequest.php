<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProdutoAdminRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->hasRole('Admin') ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $id = $this->route('id');
        
        return [
            'descricao' => ['required', 'string', 'max:255'],
            'referencia' => ['nullable', 'string', 'max:50', Rule::unique('produtos')->ignore($id)],
            'categoria_id' => ['required', 'exists:categorias,id'],
            'valor_unitario' => ['required', 'numeric', 'min:0', 'max:999999.99'],
            'valor_atacado' => ['nullable', 'numeric', 'min:0', 'max:999999.99'],
            'preco_promocional' => ['nullable', 'numeric', 'min:0', 'max:999999.99'],
            'ipi' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'quantidade' => ['required', 'integer', 'min:0'],
            'ativo' => ['nullable', 'boolean'],
            'destaque' => ['nullable', 'boolean'],
            'novo' => ['nullable', 'boolean'],
            'mais_vendido' => ['nullable', 'boolean'],
            'imagem' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:2048'],
            'imagens.*' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:2048'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'descricao.required' => 'A descrição do produto é obrigatória.',
            'categoria_id.required' => 'Selecione uma categoria.',
            'categoria_id.exists' => 'Categoria inválida.',
            'valor_unitario.required' => 'O preço unitário é obrigatório.',
            'quantidade.required' => 'A quantidade é obrigatória.',
            'imagem.image' => 'O arquivo deve ser uma imagem.',
            'imagem.max' => 'A imagem não pode ter mais que 2MB.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'ativo' => $this->boolean('ativo', true),
            'destaque' => $this->boolean('destaque'),
            'novo' => $this->boolean('novo'),
            'mais_vendido' => $this->boolean('mais_vendido'),
        ]);
    }
}