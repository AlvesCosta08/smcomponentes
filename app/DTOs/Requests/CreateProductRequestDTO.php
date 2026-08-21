<?php

namespace App\DTOs\Requests;

use App\DTOs\ProductDTO;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CreateProductRequestDTO extends ProductDTO
{
    /**
     * Converter valor monetário brasileiro para float
     * Exemplo: "1.719,00" => 1719.00 ou "17,19" => 17.19
     */
    private function parseMoney(?string $value): ?float
    {
        if (empty($value) || $value === '0,00' || $value === '0.00' || $value === '') {
            return null;
        }

        // Remove espaços em branco
        $value = trim($value);
        
        // Se o valor já estiver no formato americano (ex: 17.19)
        if (preg_match('/^\d+\.\d{1,2}$/', $value)) {
            return (float) $value;
        }

        // Formato brasileiro: 1.719,00 ou 17,19
        // Remove pontos de milhar e troca vírgula por ponto decimal
        $cleaned = str_replace('.', '', $value);
        $cleaned = str_replace(',', '.', $cleaned);
        
        return (float) $cleaned;
    }

    /**
     * Parseia um valor percentual (pode vir com vírgula)
     */
    private function parsePercent(?string $value): ?float
    {
        if (empty($value) || $value === '') {
            return null;
        }

        $value = trim($value);
        $value = str_replace('%', '', $value);
        $value = str_replace(',', '.', $value);
        
        return (float) $value;
    }

    public static function fromRequest(Request $request): self
    {
        // Criar uma instância temporária para usar os métodos parse
        $temp = new self();
        
        // Obter os valores do request
        $valorCompra = $temp->parseMoney($request->input('valor_compra'));
        $precoPromocional = $temp->parseMoney($request->input('preco_promocional'));
        $ipi = $temp->parsePercent($request->input('ipi')) ?? 9.75;
        $margemLucro = $temp->parsePercent($request->input('margem_lucro')) ?? 80;
        
        $dto = new self(
            descricao: $request->input('descricao'),
            categoria: $request->input('categoria'),
            referencia: $request->input('referencia'),
            slug: $request->input('slug') ?? Str::slug($request->input('descricao')),
            tipo: $request->input('tipo'),
            
            // Estoque
            quantidade: (int) $request->input('quantidade', 0),
            estoque_minimo: (int) $request->input('estoque_minimo', 5),
            
            // ✅ PREÇOS - CORRIGIDO
            valor_compra: $valorCompra,
            valor_atacado: null,
            valor_unitario: null,
            valor_custo: null,
            preco_promocional: $precoPromocional,
            
            // ✅ IPI e Margem
            ipi: $ipi,
            margem_lucro: $margemLucro,
            percentual_custo: null,
            
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