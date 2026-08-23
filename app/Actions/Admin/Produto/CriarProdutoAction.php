<?php

namespace App\Actions\Admin\Produto;

use App\DTOs\Requests\CreateProductRequestDTO;
use App\Domain\Produtos\Repositories\ProdutoRepositoryInterface;
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
        // 1. Processar Infraestrutura (Upload de Imagem)
        $caminhoImagem = $dto->imagem ? $this->imageUploader->upload($dto->imagem) : null;

        // 2. Preparar dados para o Domínio/Repositório
        $dados = $dto->toArray();
        $dados['imagem'] = $caminhoImagem;

        // 3. Delegar a persistência. 
        // O Model (via evento 'creating') ou o Repository aplicará as regras de domínio:
        // - Geração de slug único
        // - Validação da margem (60% a 150%) via PricingCalculator
        // - Cálculo automático de valor_custo, valor_atacado e percentual_custo
        // - Definição da disponibilidade baseada no estoque
        $produto = $this->repository->criar($dados);

        return $produto;
    }
}