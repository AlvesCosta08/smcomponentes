<?php

namespace App\Actions\Admin\Produto;

use App\Models\Produto;
use App\Repositories\ProdutoRepository;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class DeletarProdutoAction
{
    protected ProdutoRepository $repository;

    public function __construct(ProdutoRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Executar exclusão do produto
     */
    public function executar(int $id): bool
    {
        $produto = $this->repository->buscarPorId($id);

        if (!$produto) {
            return false;
        }

        // Verificar se o produto pode ser deletado
        // Ex: Não deletar se tiver pedidos pendentes
        if ($this->produtoTemPedidosPendentes($produto)) {
            throw new \Exception('Não é possível deletar um produto que possui pedidos pendentes.');
        }

        // Deletar imagem
        if ($produto->imagem) {
            Storage::disk('public')->delete($produto->imagem);
        }

        // Soft delete
        $deletado = $this->repository->deletar($produto);

        // Log de atividade
        if ($deletado) {
            Log::info('Produto deletado', [
                'produto_id' => $produto->id,
                'descricao' => $produto->descricao,
                'usuario' => auth()->user()->email ?? 'sistema'
            ]);
        }

        return $deletado;
    }

    /**
     * Verificar se o produto tem pedidos pendentes
     */
    private function produtoTemPedidosPendentes(Produto $produto): bool
    {
        return $produto->itensPedido()
            ->whereHas('pedido', function($query) {
                $query->whereIn('status', ['pendente', 'pago', 'processando']);
            })
            ->exists();
    }
}