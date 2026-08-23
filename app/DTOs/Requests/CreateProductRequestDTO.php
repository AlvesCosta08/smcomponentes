<?php

namespace App\DTOs\Requests;

use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

final readonly class CreateProductRequestDTO
{
    public function __construct(
        public string $descricao,
        public ?string $categoria,
        public ?string $referencia,
        public string $slug,
        public ?string $tipo,
        public int $quantidade,
        public int $estoque_minimo,
        public float $valor_compra,
        public float $margem_lucro,
        public float $ipi,
        public ?float $preco_promocional,
        public bool $ativo,
        public bool $destaque,
        public bool $novo,
        public bool $mais_vendido,
        public ?string $data_compra,
        public ?UploadedFile $imagem,
        public array $galeria_imagens,
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            descricao: $request->input('descricao'),
            categoria: $request->input('categoria'),
            referencia: $request->input('referencia'),
            slug: $request->input('slug') ?: Str::slug($request->input('descricao')),
            tipo: $request->input('tipo'),
            quantidade: (int) $request->input('quantidade', 0),
            estoque_minimo: (int) $request->input('estoque_minimo', 5),
            valor_compra: (float) $request->input('valor_compra', 0),
            margem_lucro: (float) $request->input('margem_lucro', 80),
            ipi: (float) $request->input('ipi', 0),
            preco_promocional: $request->has('preco_promocional') ? (float) $request->input('preco_promocional') : null,
            ativo: (bool) $request->input('ativo', true),
            destaque: (bool) $request->input('destaque', false),
            novo: (bool) $request->input('novo', false),
            mais_vendido: (bool) $request->input('mais_vendido', false),
            data_compra: $request->input('data_compra'),
            imagem: $request->file('imagem'),
            galeria_imagens: $request->file('imagens') ?? [],
        );
    }

    public function toArray(): array
    {
        return [
            'descricao' => $this->descricao,
            'categoria' => $this->categoria,
            'referencia' => $this->referencia,
            'slug' => $this->slug,
            'tipo' => $this->tipo,
            'quantidade' => $this->quantidade,
            'estoque_minimo' => $this->estoque_minimo,
            'valor_compra' => $this->valor_compra,
            'margem_lucro' => $this->margem_lucro,
            'ipi' => $this->ipi,
            'preco_promocional' => $this->preco_promocional,
            'ativo' => $this->ativo,
            'destaque' => $this->destaque,
            'novo' => $this->novo,
            'mais_vendido' => $this->mais_vendido,
            'data_compra' => $this->data_compra,
        ];
    }
}