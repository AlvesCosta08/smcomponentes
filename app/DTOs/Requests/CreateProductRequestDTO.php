<?php

namespace App\DTOs\Requests;

use App\DTOs\ProductDTO;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CreateProductRequestDTO extends ProductDTO
{
    public static function fromRequest(Request $request): self
    {
        $dto = new self(
            descricao: $request->input('descricao'),
            categoria: $request->input('categoria'),
            referencia: $request->input('referencia'),
            slug: $request->input('slug') ?? Str::slug($request->input('descricao')),
            tipo: $request->input('tipo'),
            
            // Estoque
            quantidade: (int) $request->input('quantidade', 0),
            estoque_minimo: (int) $request->input('estoque_minimo', 5),
            
            // ✅ PREÇOS - valor_compra é obrigatório, os outros são calculados
            valor_compra: $request->has('valor_compra') ? (float) $request->input('valor_compra') : null,
            valor_atacado: null, // Será calculado
            valor_unitario: null, // Será calculado
            valor_custo: null, // Será calculado
            preco_promocional: $request->has('preco_promocional') ? (float) $request->input('preco_promocional') : null,
            
            // ✅ IPI e Margem
            ipi: $request->has('ipi') ? (float) $request->input('ipi') : 9.75,
            margem_lucro: $request->has('margem_lucro') ? (float) $request->input('margem_lucro') : 80,
            percentual_custo: null, // Será calculado
            
            // Status
            disponibilidade: $request->input('disponibilidade', 'DISPONIVEL'),
            ativo: filter_var($request->input('ativo', true), FILTER_VALIDATE_BOOLEAN),
            destaque: filter_var($request->input('destaque', false), FILTER_VALIDATE_BOOLEAN),
            novo: filter_var($request->input('novo', false), FILTER_VALIDATE_BOOLEAN),
            mais_vendido: filter_var($request->input('mais_vendido', false), FILTER_VALIDATE_BOOLEAN),
            
            // Datas
            data_compra: $request->input('data_compra'),
            
            // Imagens
            imagem_file: $request->file('imagem'),
            galeria_imagens: $request->file('imagens', []),
            remover_imagem: filter_var($request->input('remover_imagem', false), FILTER_VALIDATE_BOOLEAN),
            
            // Métricas
            visualizacoes: 0,
        );

        // ✅ CALCULA OS PREÇOS AUTOMATICAMENTE
        $precos = $dto->calcularPrecos();
        $dto->valor_custo = $precos['valor_custo'];
        $dto->valor_atacado = $precos['valor_atacado'];
        $dto->percentual_custo = $precos['percentual_custo'];
        
        // Se valor_unitario não foi informado, usa o valor_atacado
        if ($dto->valor_unitario === null) {
            $dto->valor_unitario = $dto->valor_atacado;
        }

        return $dto;
    }

    /**
     * Validar os dados do DTO
     */
    public function validate(): array
    {
        $errors = [];

        if (empty($this->descricao)) {
            $errors['descricao'] = 'A descrição é obrigatória.';
        }

        if (empty($this->valor_compra) || $this->valor_compra <= 0) {
            $errors['valor_compra'] = 'O valor de compra é obrigatório e deve ser maior que zero.';
        }

        if ($this->quantidade < 0) {
            $errors['quantidade'] = 'A quantidade não pode ser negativa.';
        }

        if ($this->margem_lucro < 60 || $this->margem_lucro > 150) {
            $errors['margem_lucro'] = 'A margem de lucro deve estar entre 60% e 150%.';
        }

        if ($this->ipi < 0 || $this->ipi > 100) {
            $errors['ipi'] = 'O IPI deve estar entre 0% e 100%.';
        }

        return $errors;
    }

    /**
     * Converter para array com todos os campos calculados
     */
    public function toArray(): array
    {
        return array_merge(parent::toArray(), [
            'valor_custo' => $this->valor_custo,
            'percentual_custo' => $this->percentual_custo,
        ]);
    }
}