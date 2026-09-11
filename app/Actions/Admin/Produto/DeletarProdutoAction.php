<?php

namespace App\Actions\Admin\Produto;

use App\Interfaces\Repositories\ProdutoRepositoryInterface;
use App\Interfaces\Storage\ImageUploaderInterface;
use DomainException;

class DeletarProdutoAction
{
    public function __construct(
        private readonly ProdutoRepositoryInterface $repository,
        private readonly ImageUploaderInterface $imageUploader
    ) {}

    public function executar(int $id): bool
    {
        $produto = $this->repository->find($id);

        if (!$produto) {
            return false;
        }

        // Verifica se pode deletar (ex: sem pedidos pendentes)
        if (!$produto->podeSerDeletado()) {
            throw new DomainException('Não é possível deletar um produto que possui pedidos pendentes, pagos ou em processamento.');
        }

        if ($produto->imagem) {
            $this->imageUploader->delete($produto->imagem);
        }

        return $this->repository->delete($produto->id); // soft delete
    }
}