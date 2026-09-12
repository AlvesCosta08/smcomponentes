<?php

namespace App\Http\Requests;

use App\Enums\TipoProdutoEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

class ProdutoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('id') ?? $this->route('produto');

        return [
            // Identificação
            'descricao' => [
                'required',
                'string',
                'max:255',
            ],

            'referencia' => [
                'nullable',
                'string',
                'max:50',
                Rule::unique('produtos', 'referencia')->ignore($id),
            ],

            'categoria_id' => [
                'nullable',
                'exists:categorias,id',
            ],

            'tipo' => [
                'nullable',
                new Enum(TipoProdutoEnum::class),
            ],

            'fornecedor' => [
                'nullable',
                'string',
                'max:255',
            ],

            // Estoque
            'quantidade' => [
                'required',
                'integer',
                'min:0',
            ],

            'estoque_minimo' => [
                'nullable',
                'integer',
                'min:0',
            ],

            // Status (substitui disponibilidade)
            'status' => [
                'required',
                Rule::in(['disponivel', 'indisponivel', 'sob_encomenda']),
            ],

            // Preços (apenas unitário e promocional)
            'valor_unitario' => [
                'required',
                'numeric',
                'min:0',
            ],

            'valor_atacado' => [
                'nullable',
                'numeric',
                'min:0',
            ],

            'preco_promocional' => [
                'nullable',
                'numeric',
                'min:0',
            ],

            // Flags
            'ativo' => [
                'boolean',
            ],

            'destaque' => [
                'boolean',
            ],

            'novo' => [
                'boolean',
            ],

            'mais_vendido' => [
                'boolean',
            ],

            // Datas
            'data_compra' => [
                'nullable',
                'date',
            ],

            // Imagens
            'imagem' => [
                'nullable',
                'image',
                'max:2048',
            ],

            'imagens.*' => [
                'nullable',
                'image',
                'max:2048',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'descricao.required' => 'A descrição é obrigatória.',

            'valor_unitario.required' => 'O valor unitário é obrigatório.',
            'valor_unitario.min' => 'O valor unitário não pode ser negativo.',

            'quantidade.required' => 'A quantidade é obrigatória.',
            'quantidade.min' => 'A quantidade não pode ser negativa.',

            'status.required' => 'O status é obrigatório.',
            'status.in' => 'O status deve ser: disponivel, indisponivel ou sob_encomenda.',

            'referencia.unique' => 'Esta referência já está sendo usada.',

            'imagem.image' => 'O arquivo enviado deve ser uma imagem.',
            'imagem.max' => 'A imagem deve ter no máximo 2MB.',

            'imagens.*.image' => 'Todos os arquivos devem ser imagens.',
            'imagens.*.max' => 'Cada imagem deve ter no máximo 2MB.',
        ];
    }

    protected function prepareForValidation(): void
    {
        // Campos monetários
        $decimais = [
            'valor_unitario',
            'valor_atacado',
            'preco_promocional',
        ];

        foreach ($decimais as $campo) {
            if (!$this->has($campo) || $this->input($campo) === null) {
                continue;
            }

            $valor = (string) $this->input($campo);

            // Remove símbolo de moeda e espaços
            $valor = str_replace(['R$', ' '], '', $valor);

            // ✅ CORRIGIDO: só converte formato BR (1.234,56) SE tiver vírgula
            if (str_contains($valor, ',')) {
                $valor = str_replace('.', '', $valor);   // remove separador de milhar
                $valor = str_replace(',', '.', $valor);  // troca decimal por ponto
            }
            // Se não tem vírgula, assume formato US (99.99) — deixa como está

            $this->merge([
                $campo => is_numeric($valor) ? (float) $valor : 0,
            ]);
        }

        // Garantir que fornecedor seja nulo se vazio
        if ($this->has('fornecedor') && empty($this->input('fornecedor'))) {
            $this->merge(['fornecedor' => null]);
        }
    }
}