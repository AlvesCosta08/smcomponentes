<?php

namespace App\Repositories;

use App\Models\Produto;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProdutoRepository
{
    /**
     * Listar todas as categorias
     * 
     * @return \Illuminate\Support\Collection
     */
    public function listarCategorias()
    {
        try {
            return Produto::distinct()
                ->whereNotNull('categoria')
                ->where('categoria', '!=', '')
                ->orderBy('categoria')
                ->pluck('categoria')
                ->filter() // Remove valores vazios
                ->values(); // Reindexa o array
                
        } catch (\Exception $e) {
            Log::error('Erro ao listar categorias: ' . $e->getMessage());
            return collect([]);
        }
    }

    /**
     * Obter estatísticas dos produtos
     */
    public function obterEstatisticas(): array
    {
        try {
            return [
                'total' => Produto::count(),
                'ativos' => Produto::where('ativo', true)->count(),
                'inativos' => Produto::where('ativo', false)->count(),
                'com_estoque' => Produto::where('quantidade', '>', 0)->count(),
                'sem_estoque' => Produto::where('quantidade', '<=', 0)->count(),
                'estoque_baixo' => Produto::where('quantidade', '>', 0)
                    ->whereColumn('quantidade', '<=', 'estoque_minimo')
                    ->count(),
                'em_destaque' => Produto::where('destaque', true)->count(),
                'em_promocao' => Produto::whereNotNull('preco_promocional')
                    ->where('preco_promocional', '>', 0)
                    ->count(),
            ];
        } catch (\Exception $e) {
            Log::error('Erro ao obter estatísticas: ' . $e->getMessage());
            return [
                'total' => 0,
                'ativos' => 0,
                'inativos' => 0,
                'com_estoque' => 0,
                'sem_estoque' => 0,
                'estoque_baixo' => 0,
                'em_destaque' => 0,
                'em_promocao' => 0,
            ];
        }
    }

    /**
     * Listar produtos com filtros
     */
    public function listarProdutos($filtros = [], $porPagina = 15)
    {
        try {
            $query = Produto::query();

            // Filtro por categoria
            if (!empty($filtros['categoria'])) {
                $query->where('categoria', $filtros['categoria']);
            }

            // Filtro por status
            if (isset($filtros['ativo']) && $filtros['ativo'] !== '') {
                $query->where('ativo', $filtros['ativo']);
            }

            // Filtro por estoque
            if (isset($filtros['estoque']) && $filtros['estoque'] !== '') {
                if ($filtros['estoque'] === 'baixo') {
                    $query->where('quantidade', '>', 0)
                        ->whereColumn('quantidade', '<=', 'estoque_minimo');
                } elseif ($filtros['estoque'] === 'zerado') {
                    $query->where('quantidade', '<=', 0);
                } elseif ($filtros['estoque'] === 'disponivel') {
                    $query->where('quantidade', '>', 0);
                }
            }

            // Filtro por destaque
            if (isset($filtros['destaque']) && $filtros['destaque'] !== '') {
                $query->where('destaque', $filtros['destaque']);
            }

            // Filtro por promoção
            if (isset($filtros['promocao']) && $filtros['promocao'] !== '') {
                if ($filtros['promocao']) {
                    $query->whereNotNull('preco_promocional')
                        ->where('preco_promocional', '>', 0);
                } else {
                    $query->where(function($q) {
                        $q->whereNull('preco_promocional')
                          ->orWhere('preco_promocional', '<=', 0);
                    });
                }
            }

            // Busca por texto
            if (!empty($filtros['search'])) {
                $search = $filtros['search'];
                $query->where(function($q) use ($search) {
                    $q->where('descricao', 'LIKE', "%{$search}%")
                      ->orWhere('referencia', 'LIKE', "%{$search}%")
                      ->orWhere('categoria', 'LIKE', "%{$search}%");
                });
            }

            // Ordenação
            $ordenarPor = $filtros['ordenar_por'] ?? 'created_at';
            $ordenarDir = $filtros['ordenar_dir'] ?? 'desc';
            $query->orderBy($ordenarPor, $ordenarDir);

            return $query->paginate($porPagina);

        } catch (\Exception $e) {
            Log::error('Erro ao listar produtos: ' . $e->getMessage());
            return collect([]);
        }
    }

    /**
     * Buscar produto por ID
     */
    public function buscarPorId($id)
    {
        try {
            return Produto::findOrFail($id);
        } catch (\Exception $e) {
            Log::error('Erro ao buscar produto por ID: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Buscar produto por slug
     */
    public function buscarPorSlug($slug)
    {
        try {
            return Produto::where('slug', $slug)->firstOrFail();
        } catch (\Exception $e) {
            Log::error('Erro ao buscar produto por slug: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Criar um novo produto
     */
    public function criar(array $dados)
    {
        try {
            // Gerar slug se não for fornecido
            if (empty($dados['slug'])) {
                $dados['slug'] = \Illuminate\Support\Str::slug($dados['descricao'] . '-' . $dados['referencia']);
            }

            return Produto::create($dados);
        } catch (\Exception $e) {
            Log::error('Erro ao criar produto: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Atualizar um produto
     */
    public function atualizar($id, array $dados)
    {
        try {
            $produto = $this->buscarPorId($id);
            if (!$produto) {
                throw new \Exception('Produto não encontrado');
            }

            // Gerar slug se não for fornecido e descrição foi alterada
            if (empty($dados['slug']) && isset($dados['descricao'])) {
                $dados['slug'] = \Illuminate\Support\Str::slug($dados['descricao'] . '-' . $dados['referencia']);
            }

            $produto->update($dados);
            return $produto;
        } catch (\Exception $e) {
            Log::error('Erro ao atualizar produto: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Excluir um produto (soft delete)
     */
    public function excluir($id)
    {
        try {
            $produto = $this->buscarPorId($id);
            if (!$produto) {
                throw new \Exception('Produto não encontrado');
            }

            return $produto->delete();
        } catch (\Exception $e) {
            Log::error('Erro ao excluir produto: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Ajustar estoque do produto
     */
    public function ajustarEstoque($id, $quantidade, $tipo = 'adicionar')
    {
        try {
            $produto = $this->buscarPorId($id);
            if (!$produto) {
                throw new \Exception('Produto não encontrado');
            }

            if ($tipo === 'adicionar') {
                $produto->increment('quantidade', $quantidade);
            } else {
                $produto->decrement('quantidade', $quantidade);
            }

            // Atualizar disponibilidade
            $produto->disponibilidade = $produto->quantidade > 0 ? 'DISPONIVEL' : 'INDISPONIVEL';
            $produto->save();

            return $produto;
        } catch (\Exception $e) {
            Log::error('Erro ao ajustar estoque: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Obter produtos com estoque baixo
     */
    public function produtosEstoqueBaixo($limite = 10)
    {
        try {
            return Produto::where('ativo', true)
                ->where('quantidade', '>', 0)
                ->whereColumn('quantidade', '<=', 'estoque_minimo')
                ->orderBy('quantidade', 'asc')
                ->limit($limite)
                ->get();
        } catch (\Exception $e) {
            Log::error('Erro ao buscar produtos com estoque baixo: ' . $e->getMessage());
            return collect([]);
        }
    }

    /**
     * Obter produtos sem estoque
     */
    public function produtosSemEstoque($limite = 10)
    {
        try {
            return Produto::where('ativo', true)
                ->where('quantidade', '<=', 0)
                ->orderBy('quantidade', 'asc')
                ->limit($limite)
                ->get();
        } catch (\Exception $e) {
            Log::error('Erro ao buscar produtos sem estoque: ' . $e->getMessage());
            return collect([]);
        }
    }

    /**
     * Obter produtos em destaque
     */
    public function produtosDestaque($limite = 8)
    {
        try {
            return Produto::where('ativo', true)
                ->where('destaque', true)
                ->where('quantidade', '>', 0)
                ->orderBy('created_at', 'desc')
                ->limit($limite)
                ->get();
        } catch (\Exception $e) {
            Log::error('Erro ao buscar produtos em destaque: ' . $e->getMessage());
            return collect([]);
        }
    }

    /**
     * Obter produtos em promoção
     */
    public function produtosPromocao($limite = 8)
    {
        try {
            return Produto::where('ativo', true)
                ->whereNotNull('preco_promocional')
                ->where('preco_promocional', '>', 0)
                ->where('quantidade', '>', 0)
                ->orderBy('created_at', 'desc')
                ->limit($limite)
                ->get();
        } catch (\Exception $e) {
            Log::error('Erro ao buscar produtos em promoção: ' . $e->getMessage());
            return collect([]);
        }
    }

    /**
     * Buscar produtos por categoria
     */
    public function buscarPorCategoria($categoria, $limite = 12)
    {
        try {
            return Produto::where('ativo', true)
                ->where('categoria', $categoria)
                ->where('quantidade', '>', 0)
                ->orderBy('created_at', 'desc')
                ->limit($limite)
                ->get();
        } catch (\Exception $e) {
            Log::error('Erro ao buscar produtos por categoria: ' . $e->getMessage());
            return collect([]);
        }
    }

    /**
     * Buscar produtos similares
     */
    public function produtosSimilares($produto, $limite = 4)
    {
        try {
            return Produto::where('ativo', true)
                ->where('id', '!=', $produto->id)
                ->where('categoria', $produto->categoria)
                ->where('quantidade', '>', 0)
                ->orderBy('created_at', 'desc')
                ->limit($limite)
                ->get();
        } catch (\Exception $e) {
            Log::error('Erro ao buscar produtos similares: ' . $e->getMessage());
            return collect([]);
        }
    }
}