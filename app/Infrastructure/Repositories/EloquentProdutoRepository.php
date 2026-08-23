<?php

namespace App\Infrastructure\Repositories;

use App\Domain\Produtos\Repositories\ProdutoRepositoryInterface;
use App\Models\Produto;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;

/**
 * Implementação concreta do repositório de produtos usando Eloquent ORM.
 * 
 * Responsabilidade: Abstrair a camada de persistência de dados,
 * atendendo ao contrato definido na Interface de Domínio.
 */
class EloquentProdutoRepository implements ProdutoRepositoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function create(array $data): Produto
    {
        // O Model disparará o evento 'creating', onde o PricingCalculator 
        // e a geração de slug já estão configurados para atuar.
        return Produto::create($data);
        // 💡 DICA PRO: Se precisar invalidar cache de listas aqui, use:
        // Cache::forget('produtos_destaques_8');
    }

    /**
     * {@inheritdoc}
     */
    public function find(int $id): ?Produto
    {
        return Produto::find($id);
    }

    /**
     * {@inheritdoc}
     */
    public function findOrFail(int $id): Produto
    {
        return Produto::findOrFail($id);
    }

    /**
     * {@inheritdoc}
     */
    public function update(Produto $produto, array $data): bool
    {
        // O Model disparará o evento 'updating' para recálculos se necessário
        return $produto->update($data);
    }

    /**
     * {@inheritdoc}
     */
    public function delete(Produto $produto): bool
    {
        // Soft delete configurado no Model (devido ao trait SoftDeletes)
        return $produto->delete();
    }

    /**
     * {@inheritdoc}
     */
    public function findBySlug(string $slug): ?Produto
    {
        return Produto::where('slug', $slug)->first();
    }

    /**
     * {@inheritdoc}
     */
    public function getFiltered(array $filters, int $perPage): LengthAwarePaginator
    {
        $query = Produto::query();

        // 1. Filtro de Busca Textual (delega para o Scope do Model)
        if (!empty($filters['busca'])) {
            $query->buscar($filters['busca']);
        }

        // 2. Filtro por Categoria
        if (!empty($filters['categoria'])) {
            $query->where('categoria_id', $filters['categoria']);
        }

        // 3. Filtro por Status (delega para os Scopes do Model)
        if (!empty($filters['status'])) {
            match ($filters['status']) {
                'disponivel' => $query->disponivel(),
                'indisponivel' => $query->where('disponibilidade', \App\Enums\DisponibilidadeEnum::INDISPONIVEL->value),
                'estoque_baixo' => $query->baixoEstoque(),
                'inativo' => $query->where('ativo', false),
                default => $query, // Retorna o query builder para permitir encadeamento seguro
            };
        }

        // 4. Filtro por Faixa de Preço
        if (!empty($filters['preco_min'])) {
            $query->where('valor_atacado', '>=', (float) $filters['preco_min']);
        }
        if (!empty($filters['preco_max'])) {
            $query->where('valor_atacado', '<=', (float) $filters['preco_max']);
        }

        // 5. Ordenação
        $ordenacao = $filters['ordenar'] ?? 'created_at';
        $direcao = strtolower($filters['direcao'] ?? 'desc');
        
        $campoOrdenacao = match ($ordenacao) {
            'preco' => 'valor_atacado',
            'nome' => 'descricao',
            'popularidade' => 'visualizacoes',
            default => 'created_at',
        };

        return $query->orderBy($campoOrdenacao, $direcao)->paginate($perPage);
    }

    /**
     * {@inheritdoc}
     */
    public function getDestaques(int $limit): Collection
    {
        return Cache::remember("produtos_destaques_{$limit}", 3600, function () use ($limit) {
            return Produto::emDestaque()->limit($limit)->get();
        });
    }

    /**
     * {@inheritdoc}
     */
    public function getOfertas(int $limit): Collection
    {
        return Cache::remember("produtos_ofertas_{$limit}", 3600, function () use ($limit) {
            return Produto::ofertas()->limit($limit)->get();
        });
    }

    /**
     * {@inheritdoc}
     */
    public function getLowStock(int $threshold): Collection
    {
        return Produto::baixoEstoque($threshold)->get();
    }

    /**
     * {@inheritdoc}
     */
    public function getOutOfStock(): Collection
    {
        return Produto::where('ativo', true)
            ->where('quantidade', '<=', 0)
            ->orderBy('descricao', 'asc')
            ->get();
    }

    /**
     * {@inheritdoc}
     */
    public function incrementViews(int $id): bool
    {
        return (bool) Produto::where('id', $id)->increment('visualizacoes');
    }
}