<?php
// app/Http/Requests/Admin/BannerRequest.php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class BannerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->user()->hasRole('Admin');
    }

    public function rules(): array
    {
        $isUpdate = $this->route('banner') ? true : false;

        return [
            'titulo' => 'nullable|string|max:255',
            'subtitulo' => 'nullable|string|max:255',
            'descricao' => 'nullable|string',
            'imagem' => $isUpdate 
                ? 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048'
                : 'required_if:tipo,imagem|nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'tipo' => 'required|in:imagem,texto,misto',
            'cor_fundo' => 'nullable|string|max:255',
            'cor_texto' => 'nullable|string|max:7',
            'link' => 'nullable|url|max:255',
            'texto_botao' => 'nullable|string|max:50',
            'cor_botao' => 'nullable|string|max:7',
            'ordem' => 'nullable|integer|min:0',
            'ativo' => 'boolean',
            'inicio_em' => 'nullable|date',
            'termino_em' => 'nullable|date|after:inicio_em',
        ];
    }

    public function messages(): array
    {
        return [
            'tipo.required' => 'O tipo do banner é obrigatório.',
            'tipo.in' => 'Tipo inválido. Use: imagem, texto ou misto.',
            'imagem.required_if' => 'A imagem é obrigatória para banners do tipo imagem ou misto.',
            'imagem.image' => 'O arquivo deve ser uma imagem.',
            'imagem.mimes' => 'A imagem deve ser do tipo: jpeg, png, jpg, gif, webp.',
            'imagem.max' => 'A imagem não pode ter mais que 2MB.',
            'link.url' => 'O link deve ser uma URL válida.',
            'termino_em.after' => 'A data de término deve ser após a data de início.',
        ];
    }

    protected function prepareForValidation(): void
    {
        // Garantir que campos booleanos sejam tratados corretamente
        if ($this->has('ativo')) {
            $this->merge([
                'ativo' => filter_var($this->ativo, FILTER_VALIDATE_BOOLEAN)
            ]);
        }

        // Remover espaços extras
        if ($this->has('titulo')) {
            $this->merge([
                'titulo' => trim($this->titulo)
            ]);
        }

        // Garantir que tipo tenha um valor padrão
        if (!$this->has('tipo')) {
            $this->merge([
                'tipo' => 'imagem'
            ]);
        }
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'titulo' => 'título',
            'subtitulo' => 'subtítulo',
            'descricao' => 'descrição',
            'imagem' => 'imagem',
            'tipo' => 'tipo',
            'cor_fundo' => 'cor de fundo',
            'cor_texto' => 'cor do texto',
            'link' => 'link',
            'texto_botao' => 'texto do botão',
            'cor_botao' => 'cor do botão',
            'ordem' => 'ordem',
            'ativo' => 'ativo',
            'inicio_em' => 'data de início',
            'termino_em' => 'data de término',
        ];
    }
}