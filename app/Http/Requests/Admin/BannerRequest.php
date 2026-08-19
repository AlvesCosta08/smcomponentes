<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BannerRequest extends FormRequest
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
        $isUpdate = $this->isMethod('PUT') || $this->isMethod('PATCH');
        
        return [
            'titulo' => ['nullable', 'string', 'max:255'], // TÍTULO OPCIONAL
            'subtitulo' => ['nullable', 'string', 'max:255'],
            'descricao' => ['nullable', 'string'],
            'imagem' => $isUpdate 
                ? ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp,svg', 'max:5120']
                : ['required', 'image', 'mimes:jpeg,png,jpg,gif,webp,svg', 'max:5120'], // IMAGEM OBRIGATÓRIA
            'tipo' => ['nullable', 'string', Rule::in(['imagem', 'texto', 'misto', 'hero', 'promocional', 'informativo'])],
            'cor_fundo' => ['nullable', 'string', 'max:50'],
            'cor_texto' => ['nullable', 'string', 'max:50'],
            'link' => ['nullable', 'url', 'max:255'],
            'texto_botao' => ['nullable', 'string', 'max:50'],
            'cor_botao' => ['nullable', 'string', 'max:50'],
            'ordem' => ['nullable', 'integer', 'min:0'],
            'ativo' => ['nullable', 'boolean'],
            'inicio_em' => ['nullable', 'date'],
            'termino_em' => ['nullable', 'date', 'after_or_equal:inicio_em'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            // Imagem (OBRIGATÓRIA)
            'imagem.required' => 'A imagem do banner é obrigatória.',
            'imagem.image' => 'O arquivo deve ser uma imagem válida.',
            'imagem.mimes' => 'A imagem deve ser um dos formatos: JPEG, PNG, JPG, GIF, WEBP ou SVG.',
            'imagem.max' => 'A imagem não pode ter mais que 5MB.',
            'imagem.uploaded' => 'Falha ao enviar a imagem. Tente novamente.',
            
            // Título (OPCIONAL)
            'titulo.max' => 'O título não pode ter mais que 255 caracteres.',
            
            // Tipo
            'tipo.in' => 'O tipo selecionado não é válido.',
            
            // Link
            'link.url' => 'O link deve ser uma URL válida (ex: https://exemplo.com).',
            'link.max' => 'O link não pode ter mais que 255 caracteres.',
            
            // Datas
            'termino_em.after_or_equal' => 'A data de término deve ser após ou igual à data de início.',
            
            // Ordem
            'ordem.integer' => 'A ordem deve ser um número inteiro.',
            'ordem.min' => 'A ordem deve ser maior ou igual a 0.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'ativo' => $this->has('ativo') ? filter_var($this->ativo, FILTER_VALIDATE_BOOLEAN) : false,
            'ordem' => $this->ordem ?? 0,
        ]);
    }
}