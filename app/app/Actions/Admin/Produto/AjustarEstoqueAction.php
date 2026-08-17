<?php

namespace App\Actions\Admin\Produto;

use App\Models\Produto;
use App\Repositories\ProdutoRepository;
use Illuminate\Support\Facades\Log;

class AjustarEstoqueAction
{
    protected ProdutoRepository $repository;

    public function __construct(ProdutoRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Executar ajuste de estoque
     */
    public function executar(int $id, string $tipo, int $quantidade): bool
    {
        $produto = $this->repository->buscarPorId($id);

        if (!$produto) {
            throw new \Exception('Produto não encontrado.');
        }

        if ($tipo === 'adicionar') {
            $resultado = $produto->aumentarEstoque($quantidade);
            $mensagem = "Adicionados {$quantidade} unidades ao estoque";
        } elseif ($tipo === 'remover') {
            if (!$produto->temEstoque($quantidade)) {
                throw new \Exception('Quantidade insuficiente em estoque.');
            }
            $resultado = $produto->reduzirEstoque($quantidade);
            $mensagem = "Removidos {$quantidade} unidades do estoque";
        } else {
            throw new \Exception('Tipo de ajuste inválido.');
        }

        // Log de atividade
        if ($resultado) {
            Log::info('Estoque ajustado', [
                'produto_id' => $produto->id,
                'descricao' => $produto->descricao,
                'tipo' => $tipo,
                'quantidade' => $quantidade,
                'estoque_atual' => $produto->quantidade,
                'usuario' => auth()->user()->email ?? 'sistema'
            ]);
        }

        return $resultado;
    }
}