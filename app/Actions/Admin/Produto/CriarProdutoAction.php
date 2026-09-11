<?php

namespace App\Actions\Admin\Produto;

use App\DTOs\Requests\CreateProductRequestDTO;
use App\Interfaces\Repositories\ProdutoRepositoryInterface;
use App\Interfaces\Storage\ImageUploaderInterface;
use App\Models\Produto;

class CriarProdutoAction
{
    public function __construct(
        private readonly ProdutoRepositoryInterface $repository,
        private readonly ImageUploaderInterface $imageUploader
    ) {}

    public function executar(CreateProductRequestDTO $dto): Produto
    {
        $caminhoImagem = $dto->imagem ? $this->imageUploader->upload($dto->imagem) : null;
        $dados = $dto->toArray();
        $dados['imagem'] = $caminhoImagem;
        // O Model (boot) gerará slug e definirá status padrão
        return $this->repository->create($dados);
    }
}