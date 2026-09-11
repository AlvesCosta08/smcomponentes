<?php

namespace App\DTOs\Requests;

use App\Models\Produto;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

final readonly class UpdateProductRequestDTO
{
    public function __construct(
        public int $id,
        public string $descricao,
        public ?int $categoria_id,
        public ?string $referencia,
        public string $slug,
        public ?string $tipo,
        public string $status,
        public int $quantidade,
        public int $estoque_minimo,
        public float $valor_unitario,
        public ?float $preco_promocional,
        public bool $ativo,
        public bool $destaque,
        public bool $novo,
        public bool $mais_vendido,
        public ?string $data_compra,
        public ?string $fornecedor,
        public ?UploadedFile $imagem,
        public bool $remover_imagem_existente,
    ) {}

    public static function fromRequest(Request $request, Produto $produto): self
    {
        return new self(
            id: $produto->id,
            descricao: $request->input('descricao', $produto->descricao),
            categoria_id: $request->has('categoria_id') ? (int) $request->input('categoria_id') : $produto->categoria_id,
            referencia: $request->input('referencia', $produto->referencia),
            slug: $request->input('slug') ?: Str::slug($request->input('descricao', $produto->descricao)),
            tipo: $request->input('tipo', $produto->tipo),
            status: $request->input('status', $produto->status ?? 'indisponivel'),
            quantidade: (int) $request->input('quantidade', $produto->quantidade),
            estoque_minimo: (int) $request->input('estoque_minimo', $produto->estoque_minimo ?? 5),
            valor_unitario: (float) $request->input('valor_unitario', $produto->valor_unitario),
            preco_promocional: $request->has('preco_promocional') ? (float) $request->input('preco_promocional') : $produto->preco_promocional,
            ativo: (bool) $request->input('ativo', $produto->ativo),
            destaque: (bool) $request->input('destaque', $produto->destaque),
            novo: (bool) $request->input('novo', $produto->novo),
            mais_vendido: (bool) $request->input('mais_vendido', $produto->mais_vendido),
            data_compra: $request->input('data_compra', $produto->data_compra),
            fornecedor: $request->input('fornecedor', $produto->fornecedor),
            imagem: $request->file('imagem'),
            remover_imagem_existente: (bool) $request->input('remover_imagem', false),
        );
    }

    public function toArray(): array
    {
        return [
            'descricao' => $this->descricao,
            'categoria_id' => $this->categoria_id,
            'referencia' => $this->referencia,
            'slug' => $this->slug,
            'tipo' => $this->tipo,
            'status' => $this->status,
            'quantidade' => $this->quantidade,
            'estoque_minimo' => $this->estoque_minimo,
            'valor_unitario' => $this->valor_unitario,
            'preco_promocional' => $this->preco_promocional,
            'ativo' => $this->ativo,
            'destaque' => $this->destaque,
            'novo' => $this->novo,
            'mais_vendido' => $this->mais_vendido,
            'data_compra' => $this->data_compra,
            'fornecedor' => $this->fornecedor,
        ];
    }
}