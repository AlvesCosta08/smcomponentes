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
        // 1. Gerenciar Imagem
        if ($dto->remover_imagem_existente && $produto->imagem) {
            $this->imageUploader->delete($produto->imagem);
            $dados['imagem'] = null;
        }

        if ($dto->imagem) {
            if ($produto->imagem) {
                $this->imageUploader->delete($produto->imagem);
            }
            $dados['imagem'] = $this->imageUploader->upload($dto->imagem);
        }

        // 2. Preparar dados
        $dados = $dto->toArray();
        if (isset($dados['imagem'])) {
            $dados['imagem'] = $dados['imagem']; // Garante que a nova imagem seja salva
        }

        // 3. Delegar atualização (Model booted recalculará preços se valor_compra/margem mudarem)
        return $this->repository->atualizar($produto, $dados);
    }
}