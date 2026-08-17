<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class BannerRequest extends FormRequest
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
        $isUpdate = $this->isMethod('PUT') || $this->isMethod('PATCH');
        
        return [
            'titulo' => ['required', 'string', 'max:100'],
            'subtitulo' => ['nullable', 'string', 'max:200'],
            'descricao' => ['nullable', 'string', 'max:500'],
            'imagem' => $isUpdate 
                ? ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:2048']
                : ['required', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:2048'],
            'tipo' => ['nullable', 'string', 'in:produto,categoria,promocao,link'],
            'cor_fundo' => ['nullable', 'string', 'max:50'],
            'cor_texto' => ['nullable', 'string', 'max:50'],
            'link' => ['nullable', 'string', 'max:255'],
            'texto_botao' => ['nullable', 'string', 'max:50'],
            'cor_botao' => ['nullable', 'string', 'max:50'],
            'ordem' => ['nullable', 'integer', 'min:0'],
            'ativo' => ['nullable', 'boolean'],
            'inicio_em' => ['nullable', 'date'],
            'termino_em' => ['nullable', 'date', 'after:inicio_em'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'titulo.required' => 'O título do banner é obrigatório.',
            'imagem.required' => 'A imagem do banner é obrigatória.',
            'imagem.image' => 'O arquivo deve ser uma imagem.',
            'imagem.mimes' => 'A imagem deve ser um dos formatos: JPEG, PNG, JPG, GIF ou WEBP.',
            'imagem.max' => 'A imagem não pode ter mais que 2MB.',
            'termino_em.after' => 'A data de término deve ser após a data de início.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'ativo' => $this->boolean('ativo', true),
        ]);
    }
}