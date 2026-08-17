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

        $this->incrementarVisualizacoes($produto->id);

        return ProductResponseDTO::fromModel($produto);
    }

    /**
     * Buscar produto por slug (alias)
     */
    public function findBySlug(string $slug): ?ProductResponseDTO
    {
        return $this->getProductBySlug($slug);
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

    // ============================================
    // CRUD
    // ============================================

    /**
     * Criar novo produto
     */
    public function createProduct(ProductDTO $dto): ProductResponseDTO
    {
        try {
            DB::beginTransaction();

            $data = $dto->toArray();

            if ($dto->imagem_file) {
                $data['imagem'] = $this->uploadImage($dto->imagem_file, 'produtos');
            }

            $produto = $this->repository->create($data);

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
            $data = $dto->toArray();

            if ($dto->imagem_file) {
                if ($produto->imagem) {
                    $this->deleteImage($produto->imagem, 'produtos');
                }
                $data['imagem'] = $this->uploadImage($dto->imagem_file, 'produtos');
            }

            $updated = $this->repository->update($id, $data);

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

    // ============================================
    // ESTATÍSTICAS E LISTAGENS
    // ============================================

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
                ->where('disponibilidade', 'DISPONÍVEL')
                ->count(),
        ];
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

    // ============================================
    // FILTROS E BUSCAS
    // ============================================

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
                    WHEN disponibilidade = 'DISPONÍVEL' THEN 1
                    WHEN disponibilidade = 'EST.BAIXO' THEN 2
                    ELSE 3
                END
            ")
            ->orderBy('descricao', 'asc')
            ->paginate($perPage);

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
                    WHEN disponibilidade = 'DISPONÍVEL' THEN 1
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
     * Buscar produtos relacionados
     */
    public function getRelacionados(int $produtoId, string $categoria, int $limit = 6): array
    {
        return Produto::where('categoria', $categoria)
            ->where('id', '!=', $produtoId)
            ->where('ativo', true)
            ->where('disponibilidade', 'DISPONÍVEL')
            ->where('quantidade', '>', 0)
            ->take($limit)
            ->get()
            ->map(fn($produto) => ProductResponseDTO::fromModel($produto))
            ->toArray();
    }

    // ============================================
    // PRODUTOS EM DESTAQUE, OFERTAS, NOVOS
    // ============================================

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

    // ============================================
    // AÇÕES EM MASSA
    // ============================================

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

            $data = $original->toArray();
            unset($data['id'], $data['created_at'], $data['updated_at'], $data['deleted_at']);
            
            $slug = $original->slug . '-copia';
            $slugOriginal = $slug;
            $contador = 1;
            while (Produto::where('slug', $slug)->exists()) {
                $slug = $slugOriginal . '-' . $contador;
                $contador++;
            }
            $data['slug'] = $slug;
            
            if ($original->referencia) {
                $data['referencia'] = $original->referencia . '-COPIA';
            }

            if (!isset($data['ipi']) || $data['ipi'] === null) {
                $data['ipi'] = 9.75;
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

    // ============================================
    // EXPORTAÇÃO
    // ============================================

    /**
     * Exportar produtos para CSV (com IPI)
     */
    public function export(array $filters = []): string
    {
        $produtos = $this->repository->getProdutosComFiltros($filters, 9999);
        
        $filename = 'produtos_' . date('Y-m-d_His') . '.csv';
        $path = storage_path('app/exports/' . $filename);
        
        if (!is_dir(dirname($path))) {
            mkdir(dirname($path), 0755, true);
        }

        $handle = fopen($path, 'w');
        
        fputcsv($handle, [
            'ID', 
            'Descrição', 
            'Categoria', 
            'Referência', 
            'Quantidade', 
            'Valor Atacado', 
            'Valor Unitário',
            'IPI (%)',
            'Preço com IPI',
            'Preço Promocional',
            'Disponibilidade', 
            'Status', 
            'Criado em'
        ]);

        foreach ($produtos as $produto) {
            fputcsv($handle, [
                $produto->id,
                $produto->descricao,
                $produto->categoria,
                $produto->referencia,
                $produto->quantidade,
                number_format($produto->valor_atacado ?? 0, 2, ',', '.'),
                number_format($produto->valor_unitario ?? 0, 2, ',', '.'),
                $produto->ipi ?? 9.75,
                number_format($produto->preco_com_ipi ?? 0, 2, ',', '.'),
                $produto->preco_promocional ? number_format($produto->preco_promocional, 2, ',', '.') : '',
                $produto->disponibilidade,
                $produto->ativo ? 'Ativo' : 'Inativo',
                $produto->created_at?->format('d/m/Y H:i')
            ]);
        }

        fclose($handle);

        return $path;
    }

    /**
     * Obter produtos com filtros para exportação
     */
    public function getForExport(array $filters = []): \Illuminate\Database\Eloquent\Collection
    {
        return $this->repository->getProdutosComFiltros($filters, 9999);
    }

    /**
     * Verificar se produto existe
     */
    public function exists(int $id): bool
    {
        return $this->repository->find($id) !== null;
    }
}