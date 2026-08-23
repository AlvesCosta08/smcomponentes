<?php
// app/Services/ProductService.php

namespace App\Services;

use App\Domain\Produtos\Services\PricingCalculator;
use App\DTOs\Requests\CreateProductRequestDTO;
use App\DTOs\Requests\UpdateProductRequestDTO;
use App\DTOs\Responses\ProductResponseDTO;
use App\Interfaces\Repositories\ProdutoRepositoryInterface;
use App\Interfaces\Storage\ImageUploaderInterface;
use App\Services\Contracts\ProductServiceInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProdutoService  implements ProductServiceInterface
{
    public function __construct(
        protected ProdutoRepositoryInterface $repository,
        protected ImageUploaderInterface $imageUploader,
        protected PricingCalculator $pricingCalculator
    ) {}

    public function listProducts(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->repository->getFiltered($filters, $perPage);
    }

    public function getProductById(int $id): ?ProductResponseDTO
    {
        $produto = $this->repository->find($id);
        return $produto ? ProductResponseDTO::fromModel($produto) : null;
    }

    public function getProductBySlug(string $slug): ?ProductResponseDTO
    {
        $produto = $this->repository->findBySlug($slug);
        
        if ($produto) {
            // Delegamos o incremento de views para o repositório para manter a abstração
            $this->repository->incrementViews($produto->id);
            return ProductResponseDTO::fromModel($produto);
        }
        
        return null;
    }

    public function createProduct(CreateProductRequestDTO $dto): ProductResponseDTO
    {
        try {
            DB::beginTransaction();

            // 1. Delegar cálculo de preços ao Domain Service
            $prices = $this->pricingCalculator->calculate(
                $dto->valor_compra,
                $dto->margem_lucro,
                $dto->ipi
            );

            // 2. Preparar dados mesclando DTO + Cálculos de Domínio
            $data = array_merge($dto->toArray(), $prices);

            // 3. Delegar upload de imagem ao Infrastructure Service
            if ($dto->imagem) {
                $data['imagem'] = $this->imageUploader->upload($dto->imagem, 'produtos');
            }

            // 4. Salvar via Repositório
            $produto = $this->repository->create($data);

            DB::commit();

            Log::info('Produto criado com sucesso', [
                'produto_id' => $produto->id,
                'usuario_id' => auth()->id() ?? 'sistema'
            ]);

            return ProductResponseDTO::fromModel($produto);

        } catch (\Exception $e) {
            DB::rollBack();
            
            // Rollback da imagem em caso de falha na transação
            if (isset($data['imagem'])) {
                $this->imageUploader->delete($data['imagem']);
            }
            
            Log::error('Erro ao criar produto', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    public function updateProduct(int $id, UpdateProductRequestDTO $dto): ProductResponseDTO
    {
        try {
            DB::beginTransaction();

            $produto = $this->repository->findOrFail($id);
            $data = $dto->toArray();

            // Recalcular preços se os inputs financeiros mudaram
            if ($dto->valor_compra !== $produto->valor_compra || 
                $dto->margem_lucro !== $produto->margem_lucro || 
                $dto->ipi !== $produto->ipi) {
                
                $prices = $this->pricingCalculator->calculate($dto->valor_compra, $dto->margem_lucro, $dto->ipi);
                $data = array_merge($data, $prices);
            }

            // Gerenciar imagem
            if ($dto->remover_imagem && $produto->imagem) {
                $this->imageUploader->delete($produto->imagem);
                $data['imagem'] = null;
            } elseif ($dto->imagem) {
                $this->imageUploader->delete($produto->imagem); // Remove a antiga
                $data['imagem'] = $this->imageUploader->upload($dto->imagem, 'produtos');
            }

            $this->repository->update($produto, $data);
            DB::commit();

            return ProductResponseDTO::fromModel($produto->fresh());

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao atualizar produto', ['id' => $id, 'error' => $e->getMessage()]);
            throw $e;
        }
    }

    public function deleteProduct(int $id): bool
    {
        try {
            DB::beginTransaction();
            
            $produto = $this->repository->findOrFail($id);
            
            // Regra de Negócio: Não deletar se tiver pedidos (Exemplo de validação de domínio)
            // Isso poderia ser movido para um método $produto->canBeDeleted() no Model/Aggregate
            
            if ($produto->imagem) {
                $this->imageUploader->delete($produto->imagem);
            }

            $deleted = $this->repository->delete($produto);
            DB::commit();
            
            return $deleted;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao deletar produto', ['id' => $id, 'error' => $e->getMessage()]);
            throw $e;
        }
    }

    public function restoreProduct(int $id): bool
    {
        return $this->repository->restore($id);
    }

    public function getStats(): array
    {
        // Delegamos a busca de estatísticas para o repositório, 
        // pois é uma consulta de infraestrutura otimizada.
        return $this->repository->getStats();
    }

    public function search(string $term, int $perPage = 15): LengthAwarePaginator
    {
        return $this->repository->search($term, $perPage);
    }

    public function getByCategory(string $category, int $perPage = 15): LengthAwarePaginator
    {
        return $this->repository->findByCategory($category, $perPage);
    }

    public function exportProducts(array $filters = []): string
    {
        // Nota: Em uma arquitetura Hexagonal estrita, a exportação CSV 
        // seria uma classe dedicada em Infrastructure (ex: ProductCsvExporter).
        // Mantivemos aqui por simplicidade, mas usando o repositório.
        
        $produtos = $this->repository->getFiltered(array_merge($filters, ['export' => true]), 9999);
        $filename = 'produtos_' . now()->format('Y-m-d_His') . '.csv';
        $path = storage_path('app/exports/' . $filename);
        
        if (!is_dir(dirname($path))) {
            mkdir(dirname($path), 0755, true);
        }

        $handle = fopen($path, 'w');
        fputcsv($handle, ['ID', 'Descrição', 'Referência', 'Valor Compra', 'Valor Atacado', 'IPI (%)', 'Status']);

        foreach ($produtos as $produto) {
            fputcsv($handle, [
                $produto->id,
                $produto->descricao,
                $produto->referencia,
                number_format($produto->valor_compra, 2, ',', '.'),
                number_format($produto->valor_atacado, 2, ',', '.'),
                number_format($produto->ipi, 2, ',', '.'),
                $produto->ativo ? 'Ativo' : 'Inativo',
            ]);
        }
        fclose($handle);

        return $path;
    }
}