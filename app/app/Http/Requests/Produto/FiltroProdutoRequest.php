<?php

namespace App\Http\Requests\Produto;

use Illuminate\Foundation\Http\FormRequest;

class FiltroProdutoRequest extends FormRequest
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
     */
    public function rules(): array
    {
        return [
            'categoria' => 'nullable|exists:categorias,id',
            'status' => 'nullable|string|in:disponivel,indisponivel,estoque_baixo,todos',
            'preco_min' => 'nullable|numeric|min:0',
            'preco_max' => 'nullable|numeric|min:0|gte:preco_min',
            'destaque' => 'nullable|boolean',
            'novo' => 'nullable|boolean',
            'mais_vendido' => 'nullable|boolean',
            'busca' => 'nullable|string|max:100',
            'ordenar_por' => 'nullable|string|in:preco_asc,preco_desc,nome_asc,nome_desc,recentes,mais_vendidos',
            'page' => 'nullable|integer|min:1',
            'per_page' => 'nullable|integer|min:1|max:50',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'categoria.exists' => 'Categoria inválida.',
            'status.in' => 'Status inválido.',
            'preco_min.numeric' => 'O preço mínimo deve ser um valor numérico.',
            'preco_min.min' => 'O preço mínimo não pode ser negativo.',
            'preco_max.numeric' => 'O preço máximo deve ser um valor numérico.',
            'preco_max.gte' => 'O preço máximo deve ser maior que o preço mínimo.',
            'destaque.boolean' => 'Destaque deve ser verdadeiro ou falso.',
            'novo.boolean' => 'Novo deve ser verdadeiro ou falso.',
            'mais_vendido.boolean' => 'Mais vendido deve ser verdadeiro ou falso.',
            'busca.max' => 'A busca não pode ter mais que 100 caracteres.',
            'ordenar_por.in' => 'Opção de ordenação inválida.',
            'page.min' => 'A página deve ser pelo menos 1.',
            'per_page.min' => 'O valor deve ser pelo menos 1.',
            'per_page.max' => 'O valor não pode ser maior que 50.',
        ];
    }

    /**
     * Build the query filters.
     */
    public function getFilters(): array
    {
        $filters = [];
        
        // Filtro de categoria
        if ($this->filled('categoria')) {
            $filters['categoria_id'] = $this->categoria;
        }
        
        // Filtro de status
        if ($this->filled('status') && $this->status !== 'todos') {
            $filters['status'] = $this->status;
        }
        
        // Filtro de preço
        if ($this->filled('preco_min')) {
            $filters['preco_min'] = $this->preco_min;
        }
        
        if ($this->filled('preco_max')) {
            $filters['preco_max'] = $this->preco_max;
        }
        
        // Filtros booleanos
        if ($this->has('destaque')) {
            $filters['destaque'] = filter_var($this->destaque, FILTER_VALIDATE_BOOLEAN);
        }
        
        if ($this->has('novo')) {
            $filters['novo'] = filter_var($this->novo, FILTER_VALIDATE_BOOLEAN);
        }
        
        if ($this->has('mais_vendido')) {
            $filters['mais_vendido'] = filter_var($this->mais_vendido, FILTER_VALIDATE_BOOLEAN);
        }
        
        // Busca
        if ($this->filled('busca')) {
            $filters['busca'] = $this->busca;
        }
        
        return $filters;
    }

    /**
     * Get the sorting configuration.
     */
    public function getOrdenacao(): array
    {
        $ordenacao = [
            'campo' => 'created_at',
            'direcao' => 'desc',
        ];
        
        if ($this->filled('ordenar_por')) {
            switch ($this->ordenar_por) {
                case 'preco_asc':
                    $ordenacao = ['campo' => 'valor_atacado', 'direcao' => 'asc'];
                    break;
                case 'preco_desc':
                    $ordenacao = ['campo' => 'valor_atacado', 'direcao' => 'desc'];
                    break;
                case 'nome_asc':
                    $ordenacao = ['campo' => 'descricao', 'direcao' => 'asc'];
                    break;
                case 'nome_desc':
                    $ordenacao = ['campo' => 'descricao', 'direcao' => 'desc'];
                    break;
                case 'recentes':
                    $ordenacao = ['campo' => 'created_at', 'direcao' => 'desc'];
                    break;
                case 'mais_vendidos':
                    $ordenacao = ['campo' => 'visualizacoes', 'direcao' => 'desc'];
                    break;
            }
        }
        
        return $ordenacao;
    }

    /**
     * Get pagination configuration.
     */
    public function getPaginacao(): array
    {
        return [
            'page' => $this->page ?? 1,
            'per_page' => $this->per_page ?? 12,
        ];
    }
}