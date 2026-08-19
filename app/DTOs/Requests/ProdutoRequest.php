<?php

namespace App\Http\Requests;

use App\Enums\DisponibilidadeEnum;
use App\Enums\TipoProdutoEnum;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

class ProdutoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        $id = $this->route('id');

        return [
            // Identificação
            'descricao' => ['required', 'string', 'max:255'],
            'referencia' => ['nullable', 'string', 'max:50', Rule::unique('produtos', 'referencia')->ignore($id)],
            'categoria' => ['nullable', 'string', 'max:255'],
            'categoria_id' => ['nullable', 'exists:categorias,id'],
            'tipo' => ['nullable', new Enum(TipoProdutoEnum::class)],
            
            // Estoque
            'quantidade' => ['required', 'integer', 'min:0'],
            'estoque_minimo' => ['nullable', 'integer', 'min:0'],
            'disponibilidade' => ['nullable', new Enum(DisponibilidadeEnum::class)],
            
            // Preços
            'valor_compra' => ['required', 'numeric', 'min:0'],
            'valor_atacado' => ['nullable', 'numeric', 'min:0'],
            'valor_unitario' => ['nullable', 'numeric', 'min:0'],
            'valor_custo' => ['nullable', 'numeric', 'min:0'],
            'preco_promocional' => ['nullable', 'numeric', 'min:0'],
            
            // IPI e Margem
            'ipi' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'margem_lucro' => ['nullable', 'numeric', 'min:60', 'max:150'],
            
            // Status
            'ativo' => ['boolean'],
            'destaque' => ['boolean'],
            'novo' => ['boolean'],
            'mais_vendido' => ['boolean'],
            
            // Datas
            'data_compra' => ['nullable', 'date'],
            
            // Imagem
            'imagem' => ['nullable', 'image', 'max:2048'],
            'imagens.*' => ['nullable', 'image', 'max:2048'],
        ];
    }

    public function messages(): array
    {
        return [
            'descricao.required' => 'A descrição é obrigatória.',
            'valor_compra.required' => 'O valor de compra é obrigatório.',
            'valor_compra.min' => 'O valor de compra não pode ser negativo.',
            'quantidade.required' => 'A quantidade é obrigatória.',
            'quantidade.min' => 'A quantidade não pode ser negativa.',
            'margem_lucro.min' => 'A margem de lucro deve ser no mínimo 60%.',
            'margem_lucro.max' => 'A margem de lucro deve ser no máximo 150%.',
            'ipi.max' => 'O IPI não pode ser maior que 100%.',
            'referencia.unique' => 'Esta referência já está sendo usada.',
            'imagem.max' => 'A imagem deve ter no máximo 2MB.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $decimais = [
            'valor_compra', 
            'valor_atacado', 
            'valor_unitario', 
            'valor_custo',
            'ipi', 
            'preco_promocional', 
            'margem_lucro'
        ];
        
        foreach ($decimais as $campo) {
            if ($this->has($campo) && $this->$campo !== null) {
                $valor = str_replace(['R$', ' ', '.'], '', $this->$campo);
                $valor = str_replace(',', '.', $valor);
                
                $this->merge([
                    $campo => (float) $valor
                ]);
            }
        }
    }
}