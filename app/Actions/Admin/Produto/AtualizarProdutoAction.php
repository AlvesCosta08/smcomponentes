<?php

namespace App\Actions\Admin\Produto;

use App\DTOs\Requests\UpdateProductRequestDTO;
use App\Interfaces\Repositories\ProdutoRepositoryInterface;
use App\Interfaces\Storage\ImageUploaderInterface;
use App\Models\Produto;

class AtualizarProdutoAction
{
    public function __construct(
        private readonly ProdutoRepositoryInterface $repository,
        private readonly ImageUploaderInterface $imageUploader
    ) {}

    public function executar(Produto $produto, UpdateProductRequestDTO $dto): bool
    {
        $dados = $dto->toArray();

        // Gerenciar imagem
        if ($dto->remover_imagem_existente && $produto->imagem) {
            $this->imageUploader->delete($produto->imagem);
            $dados['imagem'] = null;
        }

        if ($dto->imagem) {
            if ($produto->imagem) $this->imageUploader->delete($produto->imagem);
            $dados['imagem'] = $this->imageUploader->upload($dto->imagem);
        } else {
            unset($dados['imagem']); // não altera a imagem se não for enviada nova
        }

        return $this->repository->update($produto->id, $dados);
    }
}