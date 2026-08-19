<?php
// app/DTOs/ProductDTO.php

namespace App\DTOs;

use App\Enums\DisponibilidadeEnum;
use App\Enums\TipoProdutoEnum;
use Illuminate\Http\UploadedFile;

readonly class ProductDTO
{
    public function __construct(
        // Dados básicos
        public ?int $id = null,
        public ?string $descricao = null,
        public ?string $categoria = null,
        public ?string $referencia = null,
        public ?string $slug = null,
        public ?TipoProdutoEnum $tipo = null,
        
        // Estoque
        public ?int $quantidade = 0,
        public ?int $estoque_minimo = 5,
        public ?DisponibilidadeEnum $disponibilidade = DisponibilidadeEnum::DISPONIVEL,
        
        // Preços
        public ?float $valor_atacado = null,
        public ?float $valor_compra = null,
        public ?float $valor_unitario = null,
        public ?float $valor_custo = null,
        public ?float $preco_promocional = null,
        
        // IPI e Margem
        public ?float $ipi = 9.75,
        public ?float $margem_lucro = 80,
        public ?float $percentual_custo = null,
        
        // Status
        public ?bool $ativo = true,
        public ?bool $destaque = false,
        public ?bool $novo = false,
        public ?bool $mais_vendido = false,
        
        // Datas
        public ?string $data_compra = null,
        
        // Imagens
        public ?UploadedFile $imagem_file = null,
        public array $galeria_imagens = [],
        public ?bool $remover_imagem = false,
        
        // Métricas
        public ?int $visualizacoes = 0,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'] ?? null,
            descricao: $data['descricao'] ?? null,
            categoria: $data['categoria'] ?? null,
            referencia: $data['referencia'] ?? null,
            slug: $data['slug'] ?? null,
            tipo: isset($data['tipo']) ? TipoProdutoEnum::tryFrom($data['tipo']) : null,
            quantidade: (int) ($data['quantidade'] ?? 0),
            estoque_minimo: (int) ($data['estoque_minimo'] ?? 5),
            disponibilidade: isset($data['disponibilidade']) 
                ? DisponibilidadeEnum::tryFrom($data['disponibilidade']) 
                : DisponibilidadeEnum::DISPONIVEL,
            valor_atacado: isset($data['valor_atacado']) ? (float) $data['valor_atacado'] : null,
            valor_compra: isset($data['valor_compra']) ? (float) $data['valor_compra'] : null,
            valor_unitario: isset($data['valor_unitario']) ? (float) $data['valor_unitario'] : null,
            valor_custo: isset($data['valor_custo']) ? (float) $data['valor_custo'] : null,
            preco_promocional: isset($data['preco_promocional']) ? (float) $data['preco_promocional'] : null,
            ipi: isset($data['ipi']) ? (float) $data['ipi'] : 9.75,
            margem_lucro: isset($data['margem_lucro']) ? (float) $data['margem_lucro'] : 80,
            percentual_custo: isset($data['percentual_custo']) ? (float) $data['percentual_custo'] : null,
            ativo: isset($data['ativo']) ? (bool) $data['ativo'] : true,
            destaque: isset($data['destaque']) ? (bool) $data['destaque'] : false,
            novo: isset($data['novo']) ? (bool) $data['novo'] : false,
            mais_vendido: isset($data['mais_vendido']) ? (bool) $data['mais_vendido'] : false,
            data_compra: $data['data_compra'] ?? null,
            imagem_file: $data['imagem_file'] ?? null,
            galeria_imagens: $data['galeria_imagens'] ?? [],
            remover_imagem: $data['remover_imagem'] ?? false,
            visualizacoes: (int) ($data['visualizacoes'] ?? 0),
        );
    }

    public static function fromModel(Produto $produto): self
    {
        return new self(
            id: $produto->id,
            descricao: $produto->descricao,
            categoria: $produto->categoria,
            referencia: $produto->referencia,
            slug: $produto->slug,
            tipo: $produto->tipo ? TipoProdutoEnum::tryFrom($produto->tipo) : null,
            quantidade: $produto->quantidade,
            estoque_minimo: $produto->estoque_minimo,
            disponibilidade: $produto->disponibilidade,
            valor_atacado: $produto->valor_atacado,
            valor_compra: $produto->valor_compra,
            valor_unitario: $produto->valor_unitario,
            valor_custo: $produto->valor_custo,
            preco_promocional: $produto->preco_promocional,
            ipi: $produto->ipi,
            margem_lucro: $produto->margem_lucro,
            percentual_custo: $produto->percentual_custo,
            ativo: (bool) $produto->ativo,
            destaque: (bool) $produto->destaque,
            novo: (bool) ($produto->novo ?? false),
            mais_vendido: (bool) ($produto->mais_vendido ?? false),
            data_compra: $produto->data_compra?->toDateString(),
            visualizacoes: $produto->visualizacoes,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'id' => $this->id,
            'descricao' => $this->descricao,
            'categoria' => $this->categoria,
            'referencia' => $this->referencia,
            'slug' => $this->slug,
            'tipo' => $this->tipo?->value,
            'quantidade' => $this->quantidade,
            'estoque_minimo' => $this->estoque_minimo,
            'disponibilidade' => $this->disponibilidade?->value ?? DisponibilidadeEnum::DISPONIVEL->value,
            'valor_atacado' => $this->valor_atacado,
            'valor_compra' => $this->valor_compra,
            'valor_unitario' => $this->valor_unitario,
            'valor_custo' => $this->valor_custo,
            'preco_promocional' => $this->preco_promocional,
            'ipi' => $this->ipi,
            'margem_lucro' => $this->margem_lucro,
            'percentual_custo' => $this->percentual_custo,
            'ativo' => $this->ativo,
            'destaque' => $this->destaque,
            'novo' => $this->novo,
            'mais_vendido' => $this->mais_vendido,
            'data_compra' => $this->data_compra,
            'visualizacoes' => $this->visualizacoes,
        ], fn($value) => $value !== null);
    }

    public function calcularPrecos(): array
    {
        $valorCompra = $this->valor_compra ?? 0;
        $margem = $this->margem_lucro ?? 80;
        $ipi = $this->ipi ?? 0;

        $custo = round($valorCompra, 2);

        $precoAtacado = 0;
        if ($margem > 0 && $margem < 100) {
            $precoAtacado = round($custo / (1 - ($margem / 100)), 2);
        } else {
            $precoAtacado = $custo;
        }

        $percentualCusto = 0;
        if ($precoAtacado > 0) {
            $percentualCusto = round(($custo / $precoAtacado) * 100, 2);
        }

        return [
            'valor_custo' => $custo,
            'valor_atacado' => $precoAtacado,
            'percentual_custo' => $percentualCusto,
        ];
    }

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
}