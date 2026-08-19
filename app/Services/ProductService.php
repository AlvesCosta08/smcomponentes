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
use Illuminate\Support\Facades\Storage;

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
        try {
            $products = $this->repository->getProdutosComFiltros(
                filters: $filters,
                perPage: $perPage
            );

            $products->getCollection()->transform(function ($product) {
                return ProductResponseDTO::fromModel($product);
            });

            return $products;
        } catch (\Exception $e) {
            Log::error('Erro ao listar produtos', [
                'filters' => $filters,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Buscar produto por ID
     */
    public function getProductById(int $id): ?ProductResponseDTO
    {
        try {
            $produto = $this->repository->find($id);
            
            if (!$produto) {
                Log::warning('Produto não encontrado', ['id' => $id]);
                return null;
            }

            return ProductResponseDTO::fromModel($produto);
        } catch (\Exception $e) {
            Log::error('Erro ao buscar produto por ID', [
                'id' => $id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Buscar produto por slug
     */
    public function getProductBySlug(string $slug): ?ProductResponseDTO
    {
        try {
            $produto = Produto::where('slug', $slug)->first();
            
            if (!$produto) {
                Log::warning('Produto não encontrado', ['slug' => $slug]);
                return null;
            }

            $this->incrementViews($produto->id);

            return ProductResponseDTO::fromModel($produto);
        } catch (\Exception $e) {
            Log::error('Erro ao buscar produto por slug', [
                'slug' => $slug,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
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
        try {
            $produto = Produto::where('referencia', $referencia)->first();
            
            if (!$produto) {
                Log::warning('Produto não encontrado', ['referencia' => $referencia]);
                return null;
            }

            return ProductResponseDTO::fromModel($produto);
        } catch (\Exception $e) {
            Log::error('Erro ao buscar produto por referência', [
                'referencia' => $referencia,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    // ============================================
    // CRUD
    // ============================================

    /**
     * Criar novo produto com imagem e cálculos
     */
    public function createProduct(ProductDTO $dto): ProductResponseDTO
    {
        try {
            DB::beginTransaction();

            $data = $dto->toArray();

            // ✅ CALCULA OS PREÇOS (IPI + MARGEM)
            $precosCalculados = $this->calcularPrecos($dto);
            $data = array_merge($data, $precosCalculados);

            // ✅ GARANTE O IPI (se não veio no DTO)
            if (!isset($data['ipi']) || $data['ipi'] === null) {
                $data['ipi'] = 9.75;
            }

            // ✅ GARANTE A MARGEM (se não veio no DTO)
            if (!isset($data['margem_lucro']) || $data['margem_lucro'] === null) {
                $data['margem_lucro'] = 80;
            }

            // Upload da imagem principal
            if ($dto->imagem_file) {
                $this->validateImage($dto->imagem_file);
                
                $data['imagem'] = $this->uploadOptimizedImage(
                    $dto->imagem_file,
                    'produtos',
                    800,
                    800,
                    85
                );
            }

            // Upload de imagens da galeria
            if (!empty($dto->galeria_imagens) && is_array($dto->galeria_imagens)) {
                $galeriaPaths = $this->uploadMultipleImages(
                    $dto->galeria_imagens, 
                    'produtos/galeria'
                );
                $data['galeria'] = json_encode($galeriaPaths);
            }

            // Criar produto
            $produto = $this->repository->create($data);

            DB::commit();

            Log::info('Produto criado com sucesso', [
                'produto_id' => $produto->id,
                'referencia' => $produto->referencia,
                'valor_compra' => $produto->valor_compra,
                'valor_atacado' => $produto->valor_atacado,
                'ipi' => $produto->ipi,
                'margem_lucro' => $produto->margem_lucro,
                'usuario_id' => auth()->id() ?? 'sistema'
            ]);

            return ProductResponseDTO::fromModel($produto);

        } catch (\Exception $e) {
            DB::rollBack();
            
            // Remove imagens em caso de erro
            $this->rollbackImages($data ?? []);
            
            Log::error('Erro ao criar produto', [
                'error' => $e->getMessage(),
                'dto' => $dto->toArray()
            ]);
            throw $e;
        }
    }

    /**
     * Atualizar produto com imagem e cálculos
     */
    public function updateProduct(int $id, ProductDTO $dto): ProductResponseDTO
    {
        try {
            DB::beginTransaction();

            $produto = $this->repository->findOrFail($id);
            $data = $dto->toArray();

            // ✅ RECALCULA OS PREÇOS
            $precosCalculados = $this->calcularPrecos($dto);
            $data = array_merge($data, $precosCalculados);

            // ✅ GARANTE O IPI
            if (!isset($data['ipi']) || $data['ipi'] === null) {
                $data['ipi'] = $produto->ipi ?? 9.75;
            }

            // ✅ GARANTE A MARGEM
            if (!isset($data['margem_lucro']) || $data['margem_lucro'] === null) {
                $data['margem_lucro'] = $produto->margem_lucro ?? 80;
            }

            // Upload da nova imagem principal
            if ($dto->imagem_file) {
                $this->validateImage($dto->imagem_file);
                
                // Remove imagem antiga
                if ($produto->imagem) {
                    $this->deleteImage($produto->imagem);
                }
                
                $data['imagem'] = $this->uploadOptimizedImage(
                    $dto->imagem_file,
                    'produtos',
                    800,
                    800,
                    85
                );
            }

            // Atualiza galeria de imagens
            if (!empty($dto->galeria_imagens) && is_array($dto->galeria_imagens)) {
                // Remove imagens antigas da galeria
                if ($produto->galeria) {
                    $oldGaleria = $this->decodeGaleria($produto->galeria);
                    if (!empty($oldGaleria)) {
                        $this->deleteMultipleImages($oldGaleria);
                    }
                }
                
                $galeriaPaths = $this->uploadMultipleImages(
                    $dto->galeria_imagens, 
                    'produtos/galeria'
                );
                $data['galeria'] = json_encode($galeriaPaths);
            }

            // Se for para remover a imagem principal
            if ($dto->remover_imagem && $produto->imagem) {
                $this->deleteImage($produto->imagem);
                $data['imagem'] = null;
            }

            $updated = $this->repository->update($id, $data);

            DB::commit();

            Log::info('Produto atualizado com sucesso', [
                'produto_id' => $id,
                'valor_compra' => $updated->valor_compra,
                'valor_atacado' => $updated->valor_atacado,
                'ipi' => $updated->ipi,
                'margem_lucro' => $updated->margem_lucro,
                'usuario_id' => auth()->id() ?? 'sistema'
            ]);

            return ProductResponseDTO::fromModel($updated);

        } catch (\Exception $e) {
            DB::rollBack();
            
            // Rollback de imagens em caso de erro
            if (isset($data['imagem']) && $data['imagem'] !== $produto->imagem ?? null) {
                $this->deleteImage($data['imagem']);
            }
            
            Log::error('Erro ao atualizar produto', [
                'error' => $e->getMessage(),
                'produto_id' => $id,
                'dto' => $dto->toArray()
            ]);
            throw $e;
        }
    }

    /**
     * Deletar produto (soft delete) com todas as imagens
     */
    public function deleteProduct(int $id): bool
    {
        try {
            DB::beginTransaction();

            $produto = $this->repository->findOrFail($id);
            
            // Remove imagem principal
            if ($produto->imagem) {
                $this->deleteImage($produto->imagem);
            }
            
            // Remove imagens da galeria
            if ($produto->galeria) {
                $galeria = $this->decodeGaleria($produto->galeria);
                if (!empty($galeria)) {
                    $this->deleteMultipleImages($galeria);
                }
            }

            $deleted = $this->repository->delete($id);

            DB::commit();

            Log::info('Produto deletado com sucesso', [
                'produto_id' => $id,
                'referencia' => $produto->referencia,
                'usuario_id' => auth()->id() ?? 'sistema'
            ]);

            return $deleted;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao deletar produto', [
                'error' => $e->getMessage(),
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
                'usuario_id' => auth()->id() ?? 'sistema'
            ]);

            return $restored;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao restaurar produto', [
                'error' => $e->getMessage(),
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
        try {
            $produto = $this->repository->findOrFail($id);

            if ($operation === 'add') {
                $this->stockService->releaseStock($produto, $quantity);
            } else {
                $this->stockService->reserveStock($produto, $quantity);
            }
            
            Log::info('Estoque ajustado com sucesso', [
                'produto_id' => $id,
                'quantidade' => $quantity,
                'operacao' => $operation
            ]);
            
            return true;
        } catch (\Exception $e) {
            Log::error('Erro ao ajustar estoque', [
                'produto_id' => $id,
                'quantidade' => $quantity,
                'operacao' => $operation,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Incrementar visualizações do produto
     */
    public function incrementViews(int $id): void
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
    // ✅ CÁLCULOS DE PREÇOS COM IPI E MARGEM
    // ============================================

    /**
     * ✅ Calcula todos os preços do produto
     * 
     * @param ProductDTO $dto
     * @return array
     */
    private function calcularPrecos(ProductDTO $dto): array
    {
        $valorCompra = (float) ($dto->valor_compra ?? 0);
        $margem = (float) ($dto->margem_lucro ?? 80);
        $ipi = (float) ($dto->ipi ?? 0);

        // 1. Valor de Custo
        $custo = round($valorCompra, 2);

        // 2. Preço Atacado (preço de venda)
        $precoAtacado = 0;
        if ($margem > 0 && $margem < 100) {
            $precoAtacado = round($custo / (1 - ($margem / 100)), 2);
        } else {
            $precoAtacado = $custo;
        }

        // 3. Percentual de Custo
        $percentualCusto = 0;
        if ($precoAtacado > 0) {
            $percentualCusto = round(($custo / $precoAtacado) * 100, 2);
        }

        // 4. Preço com IPI
        $precoComIPI = 0;
        if ($ipi > 0 && $precoAtacado > 0) {
            $precoComIPI = round($precoAtacado * (1 + ($ipi / 100)), 2);
        } else {
            $precoComIPI = $precoAtacado;
        }

        // 5. Valor do IPI
        $valorIPI = 0;
        if ($ipi > 0 && $precoAtacado > 0) {
            $valorIPI = round($precoAtacado * ($ipi / 100), 2);
        }

        // 6. Lucro Bruto
        $lucroBruto = round($precoAtacado - $custo, 2);

        // 7. Markup
        $markup = 1;
        if ($margem > 0 && $margem < 100) {
            $markup = round(1 / (1 - ($margem / 100)), 2);
        }

        return [
            'valor_custo' => $custo,
            'valor_atacado' => $precoAtacado,
            'percentual_custo' => $percentualCusto,
            'preco_com_ipi' => $precoComIPI,
            'valor_ipi' => $valorIPI,
            'lucro_bruto' => $lucroBruto,
            'markup' => $markup,
        ];
    }

    /**
     * ✅ Valida e recalcula preços de um produto existente
     */
    public function recalculatePrices(Produto $produto): Produto
    {
        $valorCompra = $produto->valor_compra ?? 0;
        $margem = $produto->margem_lucro ?? 80;
        $ipi = $produto->ipi ?? 0;

        if ($valorCompra > 0) {
            $dto = new ProductDTO(
                valor_compra: $valorCompra,
                margem_lucro: $margem,
                ipi: $ipi
            );
            
            $precos = $this->calcularPrecos($dto);
            
            $produto->valor_custo = $precos['valor_custo'];
            $produto->valor_atacado = $precos['valor_atacado'];
            $produto->percentual_custo = $precos['percentual_custo'];
            $produto->save();
        }

        return $produto;
    }

    /**
     * ✅ Recalcula preços de todos os produtos (para migração)
     */
    public function recalculateAllPrices(): array
    {
        $produtos = Produto::where('valor_compra', '>', 0)->get();
        $atualizados = 0;

        foreach ($produtos as $produto) {
            $this->recalculatePrices($produto);
            $atualizados++;
        }

        Log::info('Recálculo de preços concluído', [
            'total' => $atualizados,
            'usuario_id' => auth()->id() ?? 'sistema'
        ]);

        return [
            'total' => $produtos->count(),
            'atualizados' => $atualizados,
        ];
    }

    // ============================================
    // ESTATÍSTICAS E LISTAGENS
    // ============================================

    /**
     * Obter estatísticas dos produtos
     */
    public function getStats(): array
    {
        try {
            return [
                'total' => Produto::count(),
                'ativos' => Produto::where('ativo', true)->count(),
                'inativos' => Produto::where('ativo', false)->count(),
                'com_estoque' => Produto::where('quantidade', '>', 0)->count(),
                'sem_estoque' => Produto::where('quantidade', '<=', 0)->count(),
                'estoque_baixo' => Produto::where('quantidade', '<=', 5)
                    ->where('quantidade', '>', 0)
                    ->count(),
                'em_destaque' => Produto::where('destaque', true)->count(),
                'em_promocao' => Produto::whereNotNull('preco_promocional')
                    ->where('preco_promocional', '>', 0)
                    ->count(),
                'com_ipi' => Produto::where('ipi', '>', 0)->count(),
                'com_imagem' => Produto::whereNotNull('imagem')
                    ->where('imagem', '!=', '')
                    ->count(),
                'com_galeria' => Produto::whereNotNull('galeria')
                    ->where('galeria', '!=', '')
                    ->where('galeria', '!=', '[]')
                    ->count(),
                'disponiveis' => Produto::where('ativo', true)
                    ->where('quantidade', '>', 0)
                    ->where('disponibilidade', 'DISPONIVEL')
                    ->count(),
            ];
        } catch (\Exception $e) {
            Log::error('Erro ao obter estatísticas', [
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }

    /**
     * Obter lista de categorias
     */
    public function getCategorias(): Collection
    {
        try {
            return Produto::where('ativo', true)
                ->select('categoria')
                ->distinct()
                ->orderBy('categoria')
                ->pluck('categoria');
        } catch (\Exception $e) {
            Log::error('Erro ao obter categorias', [
                'error' => $e->getMessage()
            ]);
            return collect();
        }
    }

    /**
     * Contar produtos por categoria
     */
    public function countByCategory(): array
    {
        try {
            return Produto::where('ativo', true)
                ->select('categoria', DB::raw('count(*) as total'))
                ->groupBy('categoria')
                ->orderBy('total', 'desc')
                ->get()
                ->toArray();
        } catch (\Exception $e) {
            Log::error('Erro ao contar produtos por categoria', [
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }

    // ============================================
    // FILTROS E BUSCAS
    // ============================================

    /**
     * Buscar produtos por termo (search)
     */
    public function search(string $termo, int $perPage = 15): LengthAwarePaginator
    {
        try {
            $products = Produto::where('ativo', true)
                ->where('quantidade', '>', 0)
                ->where(function ($query) use ($termo) {
                    $query->where('descricao', 'LIKE', "%{$termo}%")
                        ->orWhere('categoria', 'LIKE', "%{$termo}%")
                        ->orWhere('referencia', 'LIKE', "%{$termo}%");
                })
                ->orderBy('descricao', 'asc')
                ->paginate($perPage);

            $products->getCollection()->transform(function ($product) {
                return ProductResponseDTO::fromModel($product);
            });

            return $products;
        } catch (\Exception $e) {
            Log::error('Erro ao buscar produtos', [
                'termo' => $termo,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Buscar produtos por categoria
     */
    public function getByCategoria(string $categoria, int $perPage = 15): LengthAwarePaginator
    {
        try {
            $products = Produto::where('categoria', 'LIKE', '%' . $categoria . '%')
                ->where('ativo', true)
                ->where('quantidade', '>', 0)
                ->orderBy('created_at', 'desc')
                ->paginate($perPage);

            $products->getCollection()->transform(function ($product) {
                return ProductResponseDTO::fromModel($product);
            });

            return $products;
        } catch (\Exception $e) {
            Log::error('Erro ao buscar produtos por categoria', [
                'categoria' => $categoria,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Buscar produtos relacionados
     */
    public function getRelacionados(int $produtoId, string $categoria, int $limit = 6): array
    {
        try {
            return Produto::where('categoria', $categoria)
                ->where('id', '!=', $produtoId)
                ->where('ativo', true)
                ->where('quantidade', '>', 0)
                ->take($limit)
                ->get()
                ->map(fn($produto) => ProductResponseDTO::fromModel($produto))
                ->toArray();
        } catch (\Exception $e) {
            Log::error('Erro ao buscar produtos relacionados', [
                'produto_id' => $produtoId,
                'categoria' => $categoria,
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }

    // ============================================
    // PRODUTOS EM DESTAQUE, OFERTAS, NOVOS
    // ============================================

    /**
     * Obter produtos em destaque
     */
    public function getDestaques(int $limit = 6): array
    {
        try {
            return Produto::where('destaque', true)
                ->where('ativo', true)
                ->orderBy('created_at', 'desc')
                ->limit($limit)
                ->get()
                ->map(fn($produto) => ProductResponseDTO::fromModel($produto))
                ->toArray();
        } catch (\Exception $e) {
            Log::error('Erro ao obter produtos em destaque', [
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }

    /**
     * Obter produtos em oferta
     */
    public function getOfertas(int $limit = 6): array
    {
        try {
            return Produto::whereNotNull('preco_promocional')
                ->where('preco_promocional', '>', 0)
                ->where('ativo', true)
                ->where('quantidade', '>', 0)
                ->orderBy('created_at', 'desc')
                ->limit($limit)
                ->get()
                ->map(fn($produto) => ProductResponseDTO::fromModel($produto))
                ->toArray();
        } catch (\Exception $e) {
            Log::error('Erro ao obter produtos em oferta', [
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }

    /**
     * Obter produtos novos
     */
    public function getNovos(int $limit = 6): array
    {
        try {
            return Produto::where('ativo', true)
                ->where('quantidade', '>', 0)
                ->orderBy('created_at', 'desc')
                ->limit($limit)
                ->get()
                ->map(fn($produto) => ProductResponseDTO::fromModel($produto))
                ->toArray();
        } catch (\Exception $e) {
            Log::error('Erro ao obter produtos novos', [
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }

    /**
     * Obter produtos com baixo estoque
     */
    public function getLowStock(int $limit = 10): LengthAwarePaginator
    {
        try {
            $products = Produto::where('quantidade', '<=', 5)
                ->where('quantidade', '>', 0)
                ->where('ativo', true)
                ->orderBy('quantidade', 'asc')
                ->paginate($limit);

            $products->getCollection()->transform(function ($product) {
                return ProductResponseDTO::fromModel($product);
            });

            return $products;
        } catch (\Exception $e) {
            Log::error('Erro ao obter produtos com baixo estoque', [
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
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
                'usuario_id' => auth()->id() ?? 'sistema'
            ]);
            
            return $updated > 0;
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro na atualização em massa', [
                'ids' => $ids,
                'data' => $data,
                'error' => $e->getMessage()
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
            
            // Gerar novo slug
            $slug = $original->slug . '-copia';
            $slugOriginal = $slug;
            $contador = 1;
            while (Produto::where('slug', $slug)->exists()) {
                $slug = $slugOriginal . '-' . $contador;
                $contador++;
            }
            $data['slug'] = $slug;
            
            // Gerar nova referência
            if ($original->referencia) {
                $data['referencia'] = $original->referencia . '-COPIA';
            }

            // ✅ Garantir IPI
            if (!isset($data['ipi']) || $data['ipi'] === null) {
                $data['ipi'] = 9.75;
            }

            // ✅ Garantir Margem
            if (!isset($data['margem_lucro']) || $data['margem_lucro'] === null) {
                $data['margem_lucro'] = 80;
            }

            // ✅ Recalcular preços
            $dto = ProductDTO::fromArray($data);
            $precos = $this->calcularPrecos($dto);
            $data = array_merge($data, $precos);

            // Resetar visualizações
            $data['visualizacoes'] = 0;

            $produto = $this->repository->create($data);

            DB::commit();

            Log::info('Produto duplicado com sucesso', [
                'original_id' => $id,
                'novo_id' => $produto->id,
                'usuario_id' => auth()->id() ?? 'sistema'
            ]);

            return ProductResponseDTO::fromModel($produto);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao duplicar produto', [
                'error' => $e->getMessage(),
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
        try {
            $produtos = $this->repository->getProdutosComFiltros($filters, 9999);
            
            $filename = 'produtos_' . date('Y-m-d_His') . '.csv';
            $path = storage_path('app/exports/' . $filename);
            
            if (!is_dir(dirname($path))) {
                mkdir(dirname($path), 0755, true);
            }

            $handle = fopen($path, 'w');
            
            // Cabeçalho
            fputcsv($handle, [
                'ID', 
                'Descrição', 
                'Categoria', 
                'Referência', 
                'Quantidade', 
                'Valor Compra',
                'Valor Custo',
                'Valor Atacado', 
                'Valor Unitário',
                'Margem Lucro (%)',
                'IPI (%)',
                'Preço com IPI',
                'Valor IPI',
                'Preço Promocional',
                'Disponibilidade', 
                'Status', 
                'Criado em',
                'Imagem URL'
            ]);

            // Dados
            foreach ($produtos as $produto) {
                // Calcula valores
                $precoComIPI = $produto->valor_atacado * (1 + ($produto->ipi / 100));
                $valorIPI = $produto->valor_atacado * ($produto->ipi / 100);

                fputcsv($handle, [
                    $produto->id,
                    $produto->descricao,
                    $produto->categoria,
                    $produto->referencia,
                    $produto->quantidade,
                    number_format($produto->valor_compra ?? 0, 2, ',', '.'),
                    number_format($produto->valor_custo ?? 0, 2, ',', '.'),
                    number_format($produto->valor_atacado ?? 0, 2, ',', '.'),
                    number_format($produto->valor_unitario ?? 0, 2, ',', '.'),
                    number_format($produto->margem_lucro ?? 80, 2, ',', '.'),
                    number_format($produto->ipi ?? 0, 2, ',', '.'),
                    number_format($precoComIPI, 2, ',', '.'),
                    number_format($valorIPI, 2, ',', '.'),
                    $produto->preco_promocional ? number_format($produto->preco_promocional, 2, ',', '.') : '',
                    $produto->disponibilidade,
                    $produto->ativo ? 'Ativo' : 'Inativo',
                    $produto->created_at?->format('d/m/Y H:i'),
                    $this->getProductImageUrl($produto->imagem)
                ]);
            }

            fclose($handle);

            Log::info('Produtos exportados com sucesso', [
                'arquivo' => $filename,
                'quantidade' => $produtos->count()
            ]);

            return $path;

        } catch (\Exception $e) {
            Log::error('Erro ao exportar produtos', [
                'error' => $e->getMessage(),
                'filters' => $filters
            ]);
            throw $e;
        }
    }

    /**
     * Obter URL da imagem do produto
     */
    public function getProductImageUrl(?string $path): ?string
    {
        if (!$path) {
            return asset('images/default-product.png');
        }

        if (filter_var($path, FILTER_VALIDATE_URL)) {
            return $path;
        }

        if (Storage::disk('public')->exists($path)) {
            return Storage::disk('public')->url($path);
        }

        return asset('images/default-product.png');
    }

    /**
     * Verificar se produto existe
     */
    public function exists(int $id): bool
    {
        try {
            return $this->repository->find($id) !== null;
        } catch (\Exception $e) {
            Log::error('Erro ao verificar existência do produto', [
                'id' => $id,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    // ============================================
    // MÉTODOS AUXILIARES PRIVADOS
    // ============================================

    /**
     * Decodificar galeria JSON para array
     */
    private function decodeGaleria($galeria): array
    {
        if (empty($galeria)) {
            return [];
        }

        if (is_string($galeria)) {
            $decoded = json_decode($galeria, true);
            return is_array($decoded) ? $decoded : [];
        }

        if (is_array($galeria)) {
            return $galeria;
        }

        return [];
    }

    /**
     * Rollback de imagens em caso de erro
     */
    private function rollbackImages(array $data): void
    {
        if (isset($data['imagem'])) {
            $this->deleteImage($data['imagem']);
        }
        
        if (isset($data['galeria'])) {
            $galeria = $this->decodeGaleria($data['galeria']);
            if (!empty($galeria)) {
                $this->deleteMultipleImages($galeria);
            }
        }
    }

    /**
     * Limpar imagens órfãs (imagens sem produto associado)
     */
    public function cleanOrphanImages(): array
    {
        try {
            $allFiles = Storage::disk('public')->files('produtos');
            $galeriaFiles = Storage::disk('public')->files('produtos/galeria');
            $allProductImages = array_merge($allFiles, $galeriaFiles);
            
            $orphanImages = [];
            $produtos = Produto::withTrashed()->get();
            
            $usedImages = [];
            foreach ($produtos as $produto) {
                if ($produto->imagem) {
                    $usedImages[] = $produto->imagem;
                }
                if ($produto->galeria) {
                    $galeria = $this->decodeGaleria($produto->galeria);
                    $usedImages = array_merge($usedImages, $galeria);
                }
            }
            
            foreach ($allProductImages as $file) {
                if (!in_array($file, $usedImages)) {
                    $orphanImages[] = $file;
                    Storage::disk('public')->delete($file);
                }
            }
            
            Log::info('Limpeza de imagens órfãs concluída', [
                'total_removidas' => count($orphanImages)
            ]);
            
            return [
                'removidas' => count($orphanImages),
                'arquivos' => $orphanImages
            ];
            
        } catch (\Exception $e) {
            Log::error('Erro ao limpar imagens órfãs', [
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }
}