<?php
// app/Repositories/ProdutoRepository.php

namespace App\Repositories;

use App\Models\Produto;
use App\Repositories\Contracts\ProdutoRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProdutoRepository extends BaseRepository implements ProdutoRepositoryInterface
{
    /**
     * {@inheritdoc}
     */
    protected function model(): string
    {
        return Produto::class;
    }

    /**
     * 🔥 NOVO MÉTODO: Buscar produtos com filtros avançados
     */
    public function getProdutosComFiltros(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $this->newQuery();
        
        // Filtro por categoria
        if (!empty($filters['categoria'])) {
            $this->query->where('categoria', $filters['categoria']);
        }
        
        // Filtro por busca
        if (!empty($filters['busca'])) {
            $search = $filters['busca'];
            $this->query->where(function($q) use ($search) {
                $q->where('descricao', 'LIKE', "%{$search}%")
                  ->orWhere('codigo', 'LIKE', "%{$search}%")
                  ->orWhere('categoria', 'LIKE', "%{$search}%");
            });
        }
        
        // Filtro por disponibilidade
        if (!empty($filters['status'])) {
            if ($filters['status'] === 'disponivel') {
                $this->query->where('disponivel', true)->where('quantidade', '>', 0);
            } elseif ($filters['status'] === 'indisponivel') {
                $this->query->where(function($q) {
                    $q->where('disponivel', false)->orWhere('quantidade', '<=', 0);
                });
            }
        }
        
        // Filtro por faixa de preço
        if (!empty($filters['preco_min'])) {
            $this->query->where('preco', '>=', $filters['preco_min']);
        }
        
        if (!empty($filters['preco_max'])) {
            $this->query->where('preco', '<=', $filters['preco_max']);
        }
        
        // Filtro por destaque
        if (!empty($filters['destaque']) && $filters['destaque'] === true) {
            $this->query->where('destaque', true);
        }
        
        // Filtro por oferta
        if (!empty($filters['oferta']) && $filters['oferta'] === true) {
            $this->query->where('oferta', true);
        }
        
        // Filtro por IDs específicos
        if (!empty($filters['ids']) && is_array($filters['ids'])) {
            $this->query->whereIn('id', $filters['ids']);
        }
        
        // Ordenação
        if (!empty($filters['ordenar'])) {
            $ordenacao = $filters['ordenar'];
            $direcao = $filters['direcao'] ?? 'asc';
            
            if ($ordenacao === 'preco') {
                $this->query->orderBy('preco', $direcao);
            } elseif ($ordenacao === 'nome') {
                $this->query->orderBy('descricao', $direcao);
            } elseif ($ordenacao === 'data') {
                $this->query->orderBy('created_at', $direcao);
            } elseif ($ordenacao === 'popularidade') {
                $this->query->orderBy('visualizacoes', 'desc');
            } else {
                $this->query->orderBy('created_at', 'desc');
            }
        } else {
            $this->query->orderBy('created_at', 'desc');
        }
        
        // Paginar resultados
        return $this->query->paginate($perPage);
    }

    /**
     * {@inheritdoc}
     */
    public function findByCategoria(string $categoria, ?int $limit = null): Collection
    {
        $this->newQuery();
        $query = $this->query->where('categoria', $categoria)
            ->where('disponivel', true)
            ->where('quantidade', '>', 0);

        if ($limit) {
            $query->limit($limit);
        }

        return $query->get();
    }

    /**
     * {@inheritdoc}
     */
    public function getDestaques(int $limit = 8): Collection
    {
        $cacheKey = 'produtos_destaques_' . $limit;

        return Cache::remember($cacheKey, 3600, function () use ($limit) {
            $this->newQuery();
            return $this->query->where('destaque', true)
                ->where('disponivel', true)
                ->where('quantidade', '>', 0)
                ->orderBy('visualizacoes', 'desc')
                ->limit($limit)
                ->get();
        });
    }

    /**
     * {@inheritdoc}
     */
    public function getOfertas(int $limit = 8): Collection
    {
        $cacheKey = 'produtos_ofertas_' . $limit;

        return Cache::remember($cacheKey, 3600, function () use ($limit) {
            $this->newQuery();
            return $this->query->where('oferta', true)
                ->where('disponivel', true)
                ->where('quantidade', '>', 0)
                ->where('preco_promocional', '>', 0)
                ->orderBy('created_at', 'desc')
                ->limit($limit)
                ->get();
        });
    }

    /**
     * {@inheritdoc}
     */
    public function getRecentes(int $limit = 8): Collection
    {
        $cacheKey = 'produtos_recentes_' . $limit;

        return Cache::remember($cacheKey, 3600, function () use ($limit) {
            $this->newQuery();
            return $this->query->where('disponivel', true)
                ->where('quantidade', '>', 0)
                ->orderBy('created_at', 'desc')
                ->limit($limit)
                ->get();
        });
    }

    /**
     * {@inheritdoc}
     */
    public function findBySlug(string $slug): ?Produto
    {
        $this->newQuery();
        return $this->query->where('slug', $slug)->first();
    }

    /**
     * {@inheritdoc}
     */
    public function getLowStock(int $threshold = 5): Collection
    {
        $this->newQuery();
        return $this->query->where('quantidade', '<=', $threshold)
            ->where('quantidade', '>', 0)
            ->orderBy('quantidade', 'asc')
            ->get();
    }

    /**
     * {@inheritdoc}
     */
    public function getOutOfStock(): Collection
    {
        $this->newQuery();
        return $this->query->where('quantidade', '<=', 0)
            ->orderBy('descricao', 'asc')
            ->get();
    }

    /**
     * {@inheritdoc}
     */
    public function getFiltered(array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        return $this->getProdutosComFiltros($filters, $perPage);
    }

    /**
     * {@inheritdoc}
     */
    public function incrementViews(int $id): bool
    {
        try {
            return (bool) $this->model->where('id', $id)->increment('visualizacoes');
        } catch (\Exception $e) {
            Log::error('Erro ao incrementar visualizações', [
                'produto_id' => $id,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getRelated(int $productId, string $categoria, int $limit = 4): Collection
    {
        $this->newQuery();
        return $this->query->where('categoria', $categoria)
            ->where('id', '!=', $productId)
            ->where('disponivel', true)
            ->where('quantidade', '>', 0)
            ->inRandomOrder()
            ->limit($limit)
            ->get();
    }

    /**
     * {@inheritdoc}
     */
    public function findByPriceRange(float $min, float $max): Collection
    {
        $this->newQuery();
        return $this->query->whereBetween('preco', [$min, $max])
            ->where('disponivel', true)
            ->orderBy('preco', 'asc')
            ->get();
    }

    /**
     * {@inheritdoc}
     */
    public function getAvailable(array $columns = ['*']): Collection
    {
        $this->newQuery();
        return $this->query->where('disponivel', true)
            ->where('quantidade', '>', 0)
            ->get($columns);
    }

    /**
     * {@inheritdoc}
     */
    public function search(string $term, int $limit = 10): Collection
    {
        $this->newQuery();
        return $this->query->where('descricao', 'LIKE', "%{$term}%")
            ->orWhere('codigo', 'LIKE', "%{$term}%")
            ->where('disponivel', true)
            ->where('quantidade', '>', 0)
            ->limit($limit)
            ->get();
    }

    /**
     * {@inheritdoc}
     */
    public function updateAvailability(int $id): bool
    {
        try {
            $produto = $this->find($id);
            if (!$produto) {
                return false;
            }

            $disponivel = $produto->quantidade > 0;
            $produto->disponivel = $disponivel;
            $produto->save();

            // Limpar cache
            $this->clearCache();

            return true;
        } catch (\Exception $e) {
            Log::error('Erro ao atualizar disponibilidade', [
                'produto_id' => $id,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getStats(): array
    {
        return [
            'total' => $this->count(),
            'disponiveis' => $this->count(['disponivel' => true, ['quantidade', '>', 0]]),
            'indisponiveis' => $this->count(['disponivel' => false]),
            'estoque_baixo' => $this->getLowStock(5)->count(),
            'sem_estoque' => $this->getOutOfStock()->count(),
            'destaques' => $this->count(['destaque' => true]),
            'ofertas' => $this->count(['oferta' => true]),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function importBatch(array $products): array
    {
        $imported = 0;
        $failed = 0;

        DB::beginTransaction();

        try {
            foreach ($products as $productData) {
                try {
                    // Verificar se já existe pelo código
                    $existing = $this->findFirstBy('codigo', $productData['codigo']);

                    if ($existing) {
                        // Atualizar
                        $this->update($existing->id, $productData);
                    } else {
                        // Criar
                        $this->create($productData);
                    }

                    $imported++;
                } catch (\Exception $e) {
                    $failed++;
                    Log::warning('Erro ao importar produto', [
                        'produto' => $productData,
                        'error' => $e->getMessage()
                    ]);
                }
            }

            DB::commit();

            // Limpar cache após importação
            $this->clearCache();

            return [
                'imported' => $imported,
                'failed' => $failed
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro na importação em lote', [
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Limpar cache de produtos
     */
    protected function clearCache(): void
    {
        Cache::tags(['produtos'])->flush();
    }
}