<?php
// app/DTOs/WishlistDTO.php

namespace App\DTOs;

use App\Models\Wishlist;
use Illuminate\Http\Request;

class WishlistDTO
{
    public function __construct(
        public readonly ?int $id,
        public readonly int $user_id,
        public readonly string $nome,
        public readonly bool $is_default,
        public readonly bool $is_public,
        public readonly ?string $descricao = null,
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            id: null,
            user_id: auth()->id(),
            nome: $request->input('nome', 'Minha Lista de Desejos'),
            is_default: filter_var($request->input('is_default', false), FILTER_VALIDATE_BOOLEAN),
            is_public: filter_var($request->input('is_public', false), FILTER_VALIDATE_BOOLEAN),
            descricao: $request->input('descricao'),
        );
    }

    public static function fromModel(Wishlist $wishlist): self
    {
        return new self(
            id: $wishlist->id,
            user_id: $wishlist->user_id,
            nome: $wishlist->nome,
            is_default: (bool) $wishlist->is_default,
            is_public: (bool) $wishlist->is_public,
            descricao: $wishlist->descricao,
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'nome' => $this->nome,
            'is_default' => $this->is_default,
            'is_public' => $this->is_public,
            'descricao' => $this->descricao,
        ];
    }
}