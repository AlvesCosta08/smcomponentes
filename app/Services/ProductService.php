<?php
// app/Services/ProductService.php

namespace App\Services;

use App\DTOs\ProductDTO;
use App\DTOs\Responses\ProductResponseDTO;
use App\Models\Produto;
use App\Repositories\ProdutoRepository;
use App\Services\Traits\ImageUploadTrait;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProductService
{
    use ImageUploadTrait;

    public function __construct(
        protected ProdutoRepository $repository,
        protected StockService $stockService
    ) {}

    /**
     * Listar produtos com filtros
     */
    public function listProducts(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $products = $this->repository->getProdutosComFiltros(
            filters: $filters,
            perPage: $perPage
        );

        // Transformar cada produto em DTO
        $products->getCollection()->transform(function ($product) {
            return ProductResponseDTO::fromModel($product);
        });

        return $products;
    }

    /**
     * Buscar produto por ID
     */
    public function getProductById(int $id): ?ProductResponseDTO
    {
        $produto = $this->repository->find($id);
        
        if (!$produto) {
            Log::warning('Produto não encontrado', ['id' => $id]);
            return null;
        }

        return ProductResponseDTO::fromModel($produto);
    }

    /**
     * Buscar produto por slug
     */
    public function getProductBySlug(string $slug): ?ProductResponseDTO
    {
        $produto = Produto::where('slug', $slug)->first();
        
        if (!$produto) {
            Log::warning('Produto não encontrado', ['slug' => $slug]);
            return null;
        }

        // Incrementar visualizações
        $this->incrementarVisualizacoes($produto->id);

        return ProductResponseDTO::fromModel($produto);
    }

    /**
     * Buscar produto por referência (SKU)
     */
    public function getProductByReference(string $referencia): ?ProductResponseDTO
    {
        $produto = Produto::where('referencia', $referencia)->first();
        
        if (!$produto) {
            Log::warning('Produto não encontrado', ['referencia' => $referencia]);
            return null;
        }

        return ProductResponseDTO::fromModel($produto);
    }

    /**
     * Criar novo produto
     */
    public function createProduct(ProductDTO $dto): ProductResponseDTO
    {
        try {
            DB::beginTransaction();

            // Upload da imagem
            if ($dto->imagem_file) {
                $dto->imagem = $this->uploadImage($dto->imagem_file, 'produtos');
            }

            // Criar produto usando o repositório
            $produto = $this->repository->create([
                'descricao' => $dto->descricao,
                'categoria' => $dto->categoria,
                'referencia' => $dto->referencia,
                'slug' => $dto->slug,
                'tipo' => $dto->tipo,
                'disponibilidade' => $dto->disponibilidade,
                'imagem' => $dto->imagem,
                'quantidade' => $dto->quantidade,
                'estoque_minimo' => $dto->estoque_minimo,
                'valor_atacado' => $dto->valor_atacado,
                'valor_compra' => $dto->valor_compra,
                'valor_unitario' => $dto->valor_unitario,
                'valor_custo' => $dto->valor_custo,
                'preco_promocional' => $dto->preco_promocional,
                'ipi' => $dto->ipi,
                'percentual_custo' => $dto->percentual_custo,
                'margem_lucro' => $dto->margem_lucro,
                'ativo' => $dto->ativo,
                'destaque' => $dto->destaque,
                'data_compra' => $dto->data_compra,
                'novo' => $dto->novo ?? false,
                'mais_vendido' => $dto->mais_vendido ?? false,
            ]);

            DB::commit();

            Log::info('Produto criado com sucesso', [
                'produto_id' => $produto->id,
                'referencia' => $produto->referencia,
                'usuario_id' => auth()->id()
            ]);

            return ProductResponseDTO::fromModel($produto);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao criar produto: ' . $e->getMessage(), [
                'dto' => $dto->toArray()
            ]);
            throw $e;
        }
    }

    /**
     * Atualizar produto
     */
    public function updateProduct(int $id, ProductDTO $dto): ProductResponseDTO
    {
        try {
            DB::beginTransaction();

            $produto = $this->repository->findOrFail($id);

            // Upload da nova imagem se enviada
            if ($dto->imagem_file) {
                // Remover imagem antiga
                if ($produto->imagem) {
                    $this->deleteImage($produto->imagem, 'produtos');
                }
                $dto->imagem = $this->uploadImage($dto->imagem_file, 'produtos');
            }

            // Atualizar produto
            $updated = $this->repository->update($id, [
                'descricao' => $dto->descricao ?? $produto->descricao,
                'categoria' => $dto->categoria ?? $produto->categoria,
                'referencia' => $dto->referencia ?? $produto->referencia,
                'slug' => $dto->slug ?? $produto->slug,
                'tipo' => $dto->tipo ?? $produto->tipo,
                'disponibilidade' => $dto->disponibilidade ?? $produto->disponibilidade,
                'imagem' => $dto->imagem ?? $produto->imagem,
                'quantidade' => $dto->quantidade ?? $produto->quantidade,
                'estoque_minimo' => $dto->estoque_minimo ?? $produto->estoque_minimo,
                'valor_atacado' => $dto->valor_atacado ?? $produto->valor_atacado,
                'valor_compra' => $dto->valor_compra ?? $produto->valor_compra,
                'valor_unitario' => $dto->valor_unitario ?? $produto->valor_unitario,
                'valor_custo' => $dto->valor_custo ?? $produto->valor_custo,
                'preco_promocional' => $dto->preco_promocional ?? $produto->preco_promocional,
                'ipi' => $dto->ipi ?? $produto->ipi,
                'percentual_custo' => $dto->percentual_custo ?? $produto->percentual_custo,
                'margem_lucro' => $dto->margem_lucro ?? $produto->margem_lucro,
                'ativo' => $dto->ativo ?? $produto->ativo,
                'destaque' => $dto->destaque ?? $produto->destaque,
                'data_compra' => $dto->data_compra ?? $produto->data_compra,
                'novo' => $dto->novo ?? $produto->novo,
                'mais_vendido' => $dto->mais_vendido ?? $produto->mais_vendido,
            ]);

            DB::commit();

            Log::info('Produto atualizado com sucesso', [
                'produto_id' => $id,
                'usuario_id' => auth()->id()
            ]);

            return ProductResponseDTO::fromModel($updated);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao atualizar produto: ' . $e->getMessage(), [
                'produto_id' => $id,
                'dto' => $dto->toArray()
            ]);
            throw $e;
        }
    }

    /**
     * Deletar produto (soft delete)
     */
    public function deleteProduct(int $id): bool
    {
        try {
            DB::beginTransaction();

            $produto = $this->repository->findOrFail($id);
            
            // Remover imagem
            if ($produto->imagem) {
                $this->deleteImage($produto->imagem, 'produtos');
            }

            $deleted = $this->repository->delete($id);

            DB::commit();

            Log::info('Produto deletado com sucesso', [
                'produto_id' => $id,
                'usuario_id' => auth()->id()
            ]);

            return $deleted;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao deletar produto: ' . $e->getMessage(), [
                'produto_id' => $id
            ]);
            throw $e;
        }
    }

    /**
     * Restaurar produto deletado
     */
    public function restoreProduct(int $id): bool
    {
        try {
            DB::beginTransaction();

            $restored = $this->repository->restore($id);

            DB::commit();

            Log::info('Produto restaurado com sucesso', [
                'produto_id' => $id,
                'usuario_id' => auth()->id()
            ]);

            return $restored;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao restaurar produto: ' . $e->getMessage(), [
                'produto_id' => $id
            ]);
            throw $e;
        }
    }

    /**
     * Ajustar estoque do produto
     */
    public function adjustStock(int $id, int $quantity, string $operation = 'add'): bool
    {
        $produto = $this->repository->findOrFail($id);

        if ($operation === 'add') {
            return $this->stockService->releaseStock($produto, $quantity);
        }

        return $this->stockService->reserveStock($produto, $quantity);
    }

    /**
     * Incrementar visualizações do produto
     */
    public function incrementarVisualizacoes(int $id): void
    {
        try {
            $produto = $this->repository->find($id);
            if ($produto) {
                $produto->increment('visualizacoes');
            }
        } catch (\Exception $e) {
            Log::warning('Erro ao incrementar visualizações', [
                'produto_id' => $id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Obter estatísticas dos produtos
     */
    public function getStats(): array
    {
        return [
            'total' => Produto::count(),
            'ativos' => Produto::where('ativo', true)->count(),
            'inativos' => Produto::where('ativo', false)->count(),
            'com_estoque' => Produto::where('quantidade', '>', 0)->count(),
            'sem_estoque' => Produto::where('quantidade', '<=', 0)->count(),
            'estoque_baixo' => Produto::where('quantidade', '<=', 5)->where('quantidade', '>', 0)->count(),
            'em_destaque' => Produto::where('destaque', true)->count(),
            'em_promocao' => Produto::whereNotNull('preco_promocional')
                ->where('preco_promocional', '>', 0)
                ->count(),
            'disponiveis' => Produto::where('ativo', true)
                ->where('quantidade', '>', 0)
                ->where('disponibilidade', 'DISPONIVEL')
                ->count(),
        ];
    }

    /**
     * Buscar produtos relacionados
     */
    public function getRelacionados(int $produtoId, string $categoria, int $limit = 6): array
    {
        return Produto::where('categoria', $categoria)
            ->where('id', '!=', $produtoId)
            ->where('ativo', true)
            ->where('disponibilidade', 'DISPONIVEL')
            ->where('quantidade', '>', 0)
            ->take($limit)
            ->get()
            ->map(fn($produto) => ProductResponseDTO::fromModel($produto))
            ->toArray();
    }

    /**
     * Buscar produtos por termo (search)
     */
    public function search(string $termo, int $perPage = 15): LengthAwarePaginator
    {
        $products = Produto::where('ativo', true)
            ->where('quantidade', '>', 0)
            ->where(function ($query) use ($termo) {
                $query->where('descricao', 'LIKE', "%{$termo}%")
                    ->orWhere('categoria', 'LIKE', "%{$termo}%")
                    ->orWhere('referencia', 'LIKE', "%{$termo}%");
            })
            ->orderByRaw("
                CASE 
                    WHEN disponibilidade = 'DISPONIVEL' THEN 1
                    WHEN disponibilidade = 'EST.BAIXO' THEN 2
                    ELSE 3
                END
            ")
            ->orderBy('descricao', 'asc')
            ->paginate($perPage);

        // Transformar para DTO
        $products->getCollection()->transform(function ($product) {
            return ProductResponseDTO::fromModel($product);
        });

        return $products;
    }

    /**
     * Obter produtos em destaque
     */
    public function getDestaques(int $limit = 6): array
    {
        return Produto::emDestaque()
            ->limit($limit)
            ->get()
            ->map(fn($produto) => ProductResponseDTO::fromModel($produto))
            ->toArray();
    }

    /**
     * Obter produtos em oferta
     */
    public function getOfertas(int $limit = 6): array
    {
        return Produto::ofertas()
            ->limit($limit)
            ->get()
            ->map(fn($produto) => ProductResponseDTO::fromModel($produto))
            ->toArray();
    }

    /**
     * Obter produtos novos
     */
    public function getNovos(int $limit = 6): array
    {
        return Produto::novos()
            ->limit($limit)
            ->get()
            ->map(fn($produto) => ProductResponseDTO::fromModel($produto))
            ->toArray();
    }

    /**
     * Obter produtos com baixo estoque
     */
    public function getLowStock(int $limit = 10): LengthAwarePaginator
    {
        $products = Produto::baixoEstoque()
            ->orderBy('quantidade', 'asc')
            ->paginate($limit);

        $products->getCollection()->transform(function ($product) {
            return ProductResponseDTO::fromModel($product);
        });

        return $products;
    }

    /**
     * Buscar produtos por categoria
     */
    public function getByCategoria(string $categoria, int $perPage = 15): LengthAwarePaginator
    {
        $products = Produto::where('categoria', 'LIKE', '%' . $categoria . '%')
            ->where('ativo', true)
            ->where('quantidade', '>', 0)
            ->orderByRaw("
                CASE 
                    WHEN disponibilidade = 'DISPONIVEL' THEN 1
                    WHEN disponibilidade = 'EST.BAIXO' THEN 2
                    ELSE 3
                END
            ")
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        $products->getCollection()->transform(function ($product) {
            return ProductResponseDTO::fromModel($product);
        });

        return $products;
    }

    /**
     * Exportar produtos para CSV
     */
    public function export(array $filters = []): string
    {
        $produtos = $this->repository->getProdutosComFiltros($filters, 9999);
        
        $filename = 'produtos_' . date('Y-m-d_His') . '.csv';
        $path = storage_path('app/exports/' . $filename);
        
        // Garantir que o diretório existe
        if (!is_dir(dirname($path))) {
            mkdir(dirname($path), 0755, true);
        }

        $handle = fopen($path, 'w');
        
        // Cabeçalhos
        fputcsv($handle, [
            'ID', 'Descrição', 'Categoria', 'Referência', 
            'Quantidade', 'Valor Unitário', 'Preço Promocional',
            'Disponibilidade', 'Status', 'Criado em'
        ]);

        // Dados
        foreach ($produtos as $produto) {
            fputcsv($handle, [
                $produto->id,
                $produto->descricao,
                $produto->categoria,
                $produto->referencia,
                $produto->quantidade,
                $produto->valor_unitario,
                $produto->preco_promocional,
                $produto->disponibilidade,
                $produto->ativo ? 'Ativo' : 'Inativo',
                $produto->created_at->format('d/m/Y H:i')
            ]);
        }

        fclose($handle);

        return $path;
    }

    /**
     * Verificar se produto existe
     */
    public function exists(int $id): bool
    {
        return $this->repository->find($id) !== null;
    }

    /**
     * Contar produtos por categoria
     */
    public function countByCategory(): array
    {
        return Produto::where('ativo', true)
            ->select('categoria', DB::raw('count(*) as total'))
            ->groupBy('categoria')
            ->orderBy('total', 'desc')
            ->get()
            ->toArray();
    }

    /**
     * Obter lista de categorias
     */
    public function getCategorias(): Collection
    {
        return Produto::where('ativo', true)
            ->select('categoria')
            ->distinct()
            ->orderBy('categoria')
            ->pluck('categoria');
    }

    /**
     * Atualizar múltiplos produtos (ação em massa)
     */
    public function bulkUpdate(array $ids, array $data): bool
    {
        try {
            DB::beginTransaction();
            
            $updated = Produto::whereIn('id', $ids)->update($data);
            
            DB::commit();
            
            Log::info('Atualização em massa realizada', [
                'ids' => $ids,
                'data' => $data,
                'quantidade' => $updated,
                'usuario_id' => auth()->id()
            ]);
            
            return $updated > 0;
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro na atualização em massa: ' . $e->getMessage(), [
                'ids' => $ids,
                'data' => $data
            ]);
            throw $e;
        }
    }

    /**
     * Duplicar produto
     */
    public function duplicateProduct(int $id): ProductResponseDTO
    {
        try {
            DB::beginTransaction();

            $original = $this->repository->findOrFail($id);

            // Criar dados duplicados
            $data = $original->toArray();
            unset($data['id'], $data['created_at'], $data['updated_at'], $data['deleted_at']);
            
            // Gerar novo slug
            $slug = $original->slug . '-copia';
            $slugOriginal = $slug;
            $contador = 1;
            while (Produto::where('slug', $slug)->exists()) {
                $slug = $slugOriginal . '-' . $contador;
                $contador++;
            }
            $data['slug'] = $slug;
            
            // Gerar nova referência (SKU)
            if ($original->referencia) {
                $data['referencia'] = $original->referencia . '-COPIA';
            }

            $produto = $this->repository->create($data);

            DB::commit();

            Log::info('Produto duplicado com sucesso', [
                'original_id' => $id,
                'novo_id' => $produto->id,
                'usuario_id' => auth()->id()
            ]);

            return ProductResponseDTO::fromModel($produto);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao duplicar produto: ' . $e->getMessage(), [
                'produto_id' => $id
            ]);
            throw $e;
        }
    }

    /**
     * Obter produtos com filtros para exportação
     */
    public function getForExport(array $filters = []): \Illuminate\Database\Eloquent\Collection
    {
        return $this->repository->getProdutosComFiltros($filters, 9999);
    }
}