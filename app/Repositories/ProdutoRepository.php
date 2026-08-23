<?php

namespace App\Repositories;

use App\Models\Produto;
use App\Repositories\Contracts\ProdutoRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class ProdutoRepository extends BaseRepository implements ProdutoRepositoryInterface
{
    protected function model(): string
    {
        return Produto::class;
    }

    // ==========================================
    // OVERRIDES PARA TIPO FORTE (PHP 8+)
    // ==========================================
    public function find(int $id, array $columns = ['*']): ?Produto
    {
        return parent::find($id, $columns);
    }

    public function findOrFail(int $id, array $columns = ['*']): Produto
    {
        return parent::findOrFail($id, $columns);
    }

    public function create(array $data): Produto
    {
        return parent::create($data);
    }

    public function update(int $id, array $data): Produto
    {
        return parent::update($id, $data);
    }

    // ==========================================
    // MÉTODOS ESPECÍFICOS (Usando Model Scopes)
    // ==========================================

    public function findBySlug(string $slug): ?Produto
    {
        return Produto::where('slug', $slug)->first();
    }

    public function findByCategoria(string $categoria, ?int $limit = null): Collection
    {
        $query = Produto::disponivel()->where('categoria', $categoria);
        return $limit ? $query->limit($limit)->get() : $query->get();
    }

    public function getDestaques(int $limit = 8): Collection
    {
        return Cache::remember("produtos_destaques_{$limit}", 3600, function () use ($limit) {
            // ✅ Usando o Scope do Model em vez de reescrever a lógica
            return Produto::emDestaque()->limit($limit)->get();
        });
    }

    public function getOfertas(int $limit = 8): Collection
    {
        return Cache::remember("produtos_ofertas_{$limit}", 3600, function () use ($limit) {
            return Produto::ofertas()->limit($limit)->get();
        });
    }

    public function getRecentes(int $limit = 8): Collection
    {
        return Cache::remember("produtos_recentes_{$limit}", 3600, function () use ($limit) {
            return Produto::novos()->limit($limit)->get();
        });
    }

    public function getLowStock(int $threshold = 5): Collection
    {
        return Produto::baixoEstoque($threshold)->get();
    }

    public function getOutOfStock(): Collection
    {
        return Produto::where('ativo', true)->where('quantidade', '<=', 0)->orderBy('descricao', 'asc')->get();
    }

    public function getFiltered(array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        $query = Produto::query()->with(['categoria']);

        // ✅ Usando o Scope de Busca do Model
        if (!empty($filters['busca'])) {
            $query->buscar($filters['busca']);
        }

        if (!empty($filters['categoria'])) {
            $query->where('categoria_id', $filters['categoria']);
        }

        // Filtro de Status (usando scopes ou where direto)
        if (!empty($filters['status'])) {
            match ($filters['status']) {
                'disponivel' => $query->disponivel(),
                'indisponivel' => $query->where('disponibilidade', \App\Enums\DisponibilidadeEnum::INDISPONIVEL),
                'estoque_baixo' => $query->baixoEstoque(),
                'inativo' => $query->where('ativo', false),
                default => null,
            };
        }

        // ✅ CORREÇÃO: Usando 'valor_atacado' em vez de 'preco'
        if (!empty($filters['preco_min'])) {
            $query->where('valor_atacado', '>=', $filters['preco_min']);
        }
        if (!empty($filters['preco_max'])) {
            $query->where('valor_atacado', '<=', $filters['preco_max']);
        }

        if (!empty($filters['destaque'])) {
            $query->where('destaque', true);
        }

        // Ordenação
        $ordenacao = $filters['ordenar'] ?? 'created_at';
        $direcao = $filters['direcao'] ?? 'desc';
        
        $campoOrdenacao = match ($ordenacao) {
            'preco' => 'valor_atacado',
            'nome' => 'descricao',
            'popularidade' => 'visualizacoes',
            default => 'created_at',
        };

        return $query->orderBy($campoOrdenacao, $direcao)->paginate($perPage);
    }

    public function incrementViews(int $id): bool
    {
        try {
            return (bool) Produto::where('id', $id)->increment('visualizacoes');
        } catch (\Exception $e) {
            Log::error('Erro ao incrementar visualizações', ['produto_id' => $id, 'error' => $e->getMessage()]);
            return false;
        }
    }

    public function getRelated(int $productId, string $categoria, int $limit = 4): Collection
    {
        return Produto::disponivel()
            ->where('categoria', $categoria)
            ->where('id', '!=', $productId)
            ->inRandomOrder()
            ->limit($limit)
            ->get();
    }

    public function findByPriceRange(float $min, float $max): Collection
    {
        return Produto::disponivel()
            ->whereBetween('valor_atacado', [$min, $max])
            ->orderBy('valor_atacado', 'asc')
            ->get();
    }

    public function getAvailable(array $columns = ['*']): Collection
    {
        return Produto::disponivel()->get($columns);
    }

    public function search(string $term, int $limit = 10): Collection
    {
        // ✅ CORREÇÃO: 'referencia' em vez de 'codigo'
        return Produto::disponivel()
            ->where(function ($q) use ($term) {
                $q->where('descricao', 'LIKE', "%{$term}%")
                  ->orWhere('referencia', 'LIKE', "%{$term}%");
            })
            ->limit($limit)
            ->get();
    }

    public function updateAvailability(int $id): bool
    {
        try {
            $produto = $this->find($id);
            if (!$produto) return false;

            // ✅ O próprio model já tem esse método que usa o Value Object Stock
            $produto->atualizarDisponibilidade();
            $produto->save();

            $this->clearCache();
            return true;
        } catch (\Exception $e) {
            Log::error('Erro ao atualizar disponibilidade', ['produto_id' => $id, 'error' => $e->getMessage()]);
            return false;
        }
    }

    public function getStats(): array
    {
        return [
            'total' => Produto::count(),
            'disponiveis' => Produto::disponivel()->count(),
            'indisponiveis' => Produto::where('ativo', false)->count(),
            'estoque_baixo' => Produto::baixoEstoque()->count(),
            'sem_estoque' => Produto::where('ativo', true)->where('quantidade', '<=', 0)->count(),
            'destaques' => Produto::where('destaque', true)->count(),
            'ofertas' => Produto::ofertas()->count(),
        ];
    }

    public function importBatch(array $products): array
    {
        $imported = 0;
        $failed = 0;

        return $this->transaction(function () use ($products, &$imported, &$failed) {
            foreach ($products as $productData) {
                try {
                    // ✅ CORREÇÃO: 'referencia' em vez de 'codigo'
                    $existing = $this->findFirstBy('referencia', $productData['referencia']);

                    if ($existing) {
                        $this->update($existing->id, $productData);
                    } else {
                        $this->create($productData);
                    }
                    $imported++;
                } catch (\Exception $e) {
                    $failed++;
                    Log::warning('Erro ao importar produto', ['produto' => $productData, 'error' => $e->getMessage()]);
                }
            }
            
            $this->clearCache();
            return ['imported' => $imported, 'failed' => $failed];
        });
    }

    protected function clearCache(): void
    {
        // Nota: Certifique-se de que 'produtos' está configurado como tag de cache no config/cache.php
        // Se não estiver, use Cache::flush() ou remova as chaves específicas manualmente.
        if (config('cache.default') !== 'array') {
            Cache::tags(['produtos'])->flush();
        }
    }
}