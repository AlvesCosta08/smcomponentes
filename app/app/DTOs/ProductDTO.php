<?php

namespace App\DTOs;

use App\Models\Produto;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;

class ProductDTO
{
    public function __construct(
        public readonly ?int $id,
        public readonly string $descricao,
        public readonly string $categoria,
        public readonly string $referencia,
        public readonly ?string $slug,
        public readonly ?string $tipo,
        public readonly string $disponibilidade,
        public readonly ?string $imagem,
        public readonly ?UploadedFile $imagem_file,
        public readonly int $quantidade,
        public readonly int $estoque_minimo,
        public readonly ?float $valor_atacado,
        public readonly ?float $valor_compra,
        public readonly ?float $valor_unitario,
        public readonly ?float $valor_custo,
        public readonly ?float $preco_promocional,
        public readonly ?float $ipi,
        public readonly ?float $percentual_custo,
        public readonly ?float $margem_lucro,
        public readonly bool $ativo,
        public readonly bool $destaque,
        public readonly ?string $data_compra,
        public readonly ?int $visualizacoes = 0,
        public readonly ?bool $novo = false,
        public readonly ?bool $mais_vendido = false,
        public readonly ?string $created_at = null,
        public readonly ?string $updated_at = null,
    ) {}

    /**
     * Criar DTO a partir de um Request (CREATE)
     */
    public static function fromRequest(Request $request): self
    {
        return new self(
            id: null,
            descricao: $request->input('descricao'),
            categoria: $request->input('categoria'),
            referencia: $request->input('referencia'),
            slug: $request->input('slug') ?? \Illuminate\Support\Str::slug($request->input('descricao')),
            tipo: $request->input('tipo'),
            disponibilidade: $request->input('disponibilidade', 'DISPONÍVEL'),
            imagem: null,
            imagem_file: $request->file('imagem'),
            quantidade: (int) $request->input('quantidade', 0),
            estoque_minimo: (int) $request->input('estoque_minimo', 5),
            valor_atacado: $request->has('valor_atacado') ? (float) $request->input('valor_atacado') : null,
            valor_compra: $request->has('valor_compra') ? (float) $request->input('valor_compra') : null,
            valor_unitario: $request->has('valor_unitario') ? (float) $request->input('valor_unitario') : null,
            valor_custo: $request->has('valor_custo') ? (float) $request->input('valor_custo') : null,
            preco_promocional: $request->has('preco_promocional') ? (float) $request->input('preco_promocional') : null,
            ipi: $request->has('ipi') ? (float) $request->input('ipi') : 9.75,
            percentual_custo: $request->has('percentual_custo') ? (float) $request->input('percentual_custo') : null,
            margem_lucro: $request->has('margem_lucro') ? (float) $request->input('margem_lucro') : null,
            ativo: filter_var($request->input('ativo', true), FILTER_VALIDATE_BOOLEAN),
            destaque: filter_var($request->input('destaque', false), FILTER_VALIDATE_BOOLEAN),
            data_compra: $request->input('data_compra'),
            visualizacoes: 0,
            novo: filter_var($request->input('novo', false), FILTER_VALIDATE_BOOLEAN),
            mais_vendido: filter_var($request->input('mais_vendido', false), FILTER_VALIDATE_BOOLEAN),
            created_at: null,
            updated_at: null,
        );
    }

    /**
     * Criar DTO a partir de um Request (UPDATE)
     */
    public static function fromRequestUpdate(Request $request, int $id): self
    {
        return new self(
            id: $id,
            descricao: $request->input('descricao'),
            categoria: $request->input('categoria'),
            referencia: $request->input('referencia'),
            slug: $request->input('slug') ?? \Illuminate\Support\Str::slug($request->input('descricao')),
            tipo: $request->input('tipo'),
            disponibilidade: $request->input('disponibilidade', 'DISPONÍVEL'),
            imagem: $request->input('imagem_existente'),
            imagem_file: $request->file('imagem'),
            quantidade: (int) $request->input('quantidade', 0),
            estoque_minimo: (int) $request->input('estoque_minimo', 5),
            valor_atacado: $request->has('valor_atacado') ? (float) $request->input('valor_atacado') : null,
            valor_compra: $request->has('valor_compra') ? (float) $request->input('valor_compra') : null,
            valor_unitario: $request->has('valor_unitario') ? (float) $request->input('valor_unitario') : null,
            valor_custo: $request->has('valor_custo') ? (float) $request->input('valor_custo') : null,
            preco_promocional: $request->has('preco_promocional') ? (float) $request->input('preco_promocional') : null,
            ipi: $request->has('ipi') ? (float) $request->input('ipi') : 9.75,
            percentual_custo: $request->has('percentual_custo') ? (float) $request->input('percentual_custo') : null,
            margem_lucro: $request->has('margem_lucro') ? (float) $request->input('margem_lucro') : null,
            ativo: filter_var($request->input('ativo', true), FILTER_VALIDATE_BOOLEAN),
            destaque: filter_var($request->input('destaque', false), FILTER_VALIDATE_BOOLEAN),
            data_compra: $request->input('data_compra'),
            visualizacoes: (int) $request->input('visualizacoes', 0),
            novo: filter_var($request->input('novo', false), FILTER_VALIDATE_BOOLEAN),
            mais_vendido: filter_var($request->input('mais_vendido', false), FILTER_VALIDATE_BOOLEAN),
            created_at: null,
            updated_at: null,
        );
    }

    /**
     * Criar DTO a partir de um Model
     */
    public static function fromModel(Produto $produto): self
    {
        return new self(
            id: $produto->id,
            descricao: $produto->descricao,
            categoria: $produto->categoria,
            referencia: $produto->referencia,
            slug: $produto->slug,
            tipo: $produto->tipo,
            disponibilidade: $produto->disponibilidade,
            imagem: $produto->imagem,
            imagem_file: null,
            quantidade: $produto->quantidade,
            estoque_minimo: $produto->estoque_minimo ?? 5,
            valor_atacado: $produto->valor_atacado,
            valor_compra: $produto->valor_compra,
            valor_unitario: $produto->valor_unitario,
            valor_custo: $produto->valor_custo,
            preco_promocional: $produto->preco_promocional,
            ipi: $produto->ipi ?? 9.75,
            percentual_custo: $produto->percentual_custo,
            margem_lucro: $produto->margem_lucro,
            ativo: (bool) $produto->ativo,
            destaque: (bool) $produto->destaque,
            data_compra: $produto->data_compra,
            visualizacoes: $produto->visualizacoes ?? 0,
            novo: $produto->novo ?? false,
            mais_vendido: $produto->mais_vendido ?? false,
            created_at: $produto->created_at ? $produto->created_at->toDateTimeString() : null,
            updated_at: $produto->updated_at ? $produto->updated_at->toDateTimeString() : null,
        );
    }

    /**
     * Converter para Array (para API/JSON)
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'descricao' => $this->descricao,
            'categoria' => $this->categoria,
            'referencia' => $this->referencia,
            'slug' => $this->slug,
            'tipo' => $this->tipo,
            'disponibilidade' => $this->disponibilidade,
            'imagem' => $this->imagem,
            'quantidade' => $this->quantidade,
            'estoque_minimo' => $this->estoque_minimo,
            'valor_atacado' => $this->valor_atacado,
            'valor_compra' => $this->valor_compra,
            'valor_unitario' => $this->valor_unitario,
            'valor_custo' => $this->valor_custo,
            'preco_promocional' => $this->preco_promocional,
            'ipi' => $this->ipi,
            'percentual_custo' => $this->percentual_custo,
            'margem_lucro' => $this->margem_lucro,
            'ativo' => $this->ativo,
            'destaque' => $this->destaque,
            'data_compra' => $this->data_compra,
            'visualizacoes' => $this->visualizacoes,
            'novo' => $this->novo,
            'mais_vendido' => $this->mais_vendido,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'preco_formatado' => $this->getPrecoFormatado(),
            'preco_atacado_formatado' => $this->getPrecoAtacadoFormatado(),
            'preco_promocional_formatado' => $this->getPrecoPromocionalFormatado(),
            'tem_promocao' => $this->temPromocao(),
            'status' => $this->getStatus(),
            'disponivel' => $this->isDisponivel(),
        ];
    }

    // ============================================
    // MÉTODOS AUXILIARES
    // ============================================

    public function getPrecoFormatado(): string
    {
        if ($this->valor_unitario) {
            return 'R$ ' . number_format($this->valor_unitario, 2, ',', '.');
        }
        return 'R$ 0,00';
    }

    public function getPrecoAtacadoFormatado(): string
    {
        if ($this->valor_atacado) {
            return 'R$ ' . number_format($this->valor_atacado, 2, ',', '.');
        }
        return 'R$ 0,00';
    }

    public function getPrecoPromocionalFormatado(): string
    {
        if ($this->preco_promocional) {
            return 'R$ ' . number_format($this->preco_promocional, 2, ',', '.');
        }
        return '';
    }

    public function temPromocao(): bool
    {
        return $this->preco_promocional !== null 
            && $this->preco_promocional > 0 
            && $this->valor_atacado !== null
            && $this->preco_promocional < $this->valor_atacado;
    }

    public function getStatus(): string
    {
        if (!$this->ativo) {
            return 'Inativo';
        }
        if ($this->quantidade <= 0) {
            return 'Esgotado';
        }
        if ($this->disponibilidade === 'INDISPONÍVEL') {
            return 'Indisponível';
        }
        return 'Disponível';
    }

    public function isDisponivel(): bool
    {
        return $this->ativo 
            && $this->disponibilidade === 'DISPONÍVEL'
            && $this->quantidade > 0;
    }

    public function getImagemUrl(): string
    {
        if ($this->imagem) {
            return asset('storage/produtos/' . $this->imagem);
        }
        return asset('images/produto-placeholder.jpg');
    }

    public function getCreatedAtFormatted(): string
    {
        if ($this->created_at) {
            return \Carbon\Carbon::parse($this->created_at)->format('d/m/Y H:i');
        }
        return '-';
    }

    public function getUpdatedAtFormatted(): string
    {
        if ($this->updated_at) {
            return \Carbon\Carbon::parse($this->updated_at)->format('d/m/Y H:i');
        }
        return '-';
    }
}