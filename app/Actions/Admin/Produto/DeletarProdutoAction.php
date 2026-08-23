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
        $produto = $this->repository->buscarPorId($id);

        if (!$produto) {
            return false;
        }

        // Regra de Domínio: Verifica se o produto pode ser seguramente deletado
        if (!$produto->podeSerDeletado()) {
            throw new DomainException('Não é possível deletar um produto que possui pedidos pendentes, pagos ou em processamento.');
        }

        // Limpar Infraestrutura (Imagem)
        if ($produto->imagem) {
            $this->imageUploader->delete($produto->imagem);
        }

        // Executar Soft Delete via Interface
        return $this->repository->deletar($produto);
    }
}