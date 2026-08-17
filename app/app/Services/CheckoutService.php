<?php

namespace App\Services;

use App\Models\Pedido;
use App\Models\Produto;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CheckoutService
{
    /**
     * Obtém o carrinho completo com dados dos produtos.
     */
    public function getCarrinhoCompleto(): Collection
    {
        $carrinho = session()->get('carrinho', []);
        $resultado = collect();

        foreach ($carrinho as $item) {
            $produto = Produto::find($item['produto_id']);

            if (!$produto) {
                Log::warning('⚠️ Produto não encontrado no carrinho', [
                    'produto_id' => $item['produto_id']
                ]);
                continue;
            }

            $resultado->push([
                'produto_id' => $produto->id,
                'nome' => $produto->descricao,
                'slug' => $produto->slug,
                'quantidade' => $item['quantidade'],
                'preco' => $produto->valor_unitario,
                'preco_formatado' => $this->formatarMoeda($produto->valor_unitario),
                'subtotal' => $produto->valor_unitario * $item['quantidade'],
                'subtotal_formatado' => $this->formatarMoeda($produto->valor_unitario * $item['quantidade']),
                'estoque' => $produto->quantidade,
                'imagem' => $produto->imagem_url,
                'disponivel' => $produto->isDisponivel(),
            ]);
        }

        return $resultado;
    }

    /**
     * Verifica se todos os produtos têm estoque suficiente.
     */
    public function verificarEstoque(Collection $carrinho): array
    {
        foreach ($carrinho as $item) {
            $produto = Produto::find($item['produto_id']);

            if (!$produto) {
                return [
                    'valido' => false,
                    'mensagem' => "Produto não encontrado."
                ];
            }

            if (!$produto->isDisponivel()) {
                return [
                    'valido' => false,
                    'mensagem' => "Produto '{$produto->descricao}' está indisponível."
                ];
            }

            if (!$produto->temEstoque($item['quantidade'])) {
                return [
                    'valido' => false,
                    'mensagem' => "Produto '{$produto->descricao}' não tem estoque suficiente. Disponível: {$produto->quantidade}"
                ];
            }
        }

        return ['valido' => true, 'mensagem' => null];
    }

    /**
     * Calcula o subtotal do carrinho.
     */
    public function calcularSubtotal(Collection $carrinho): float
    {
        return $carrinho->sum('subtotal');
    }

    /**
     * Cria um pedido a partir do carrinho.
     */
    public function criarPedido(User $user, Collection $carrinho, string $formaPagamento): Pedido
    {
        return DB::transaction(function () use ($user, $carrinho, $formaPagamento) {
            $subtotal = $this->calcularSubtotal($carrinho);

            $pedido = Pedido::create([
                'user_id' => $user->id,
                'numero_pedido' => Pedido::gerarNumeroPedido(),
                'total' => $subtotal,
                'status' => 'pendente',
                'status_pagamento' => 'aguardando',
                'forma_pagamento' => $formaPagamento,
            ]);

            foreach ($carrinho as $item) {
                $produto = Produto::find($item['produto_id']);

                $pedido->itens()->create([
                    'produto_id' => $item['produto_id'],
                    'quantidade' => $item['quantidade'],
                    'preco_unitario' => $produto->valor_unitario,
                    'subtotal' => $produto->valor_unitario * $item['quantidade'],
                    'nome_produto' => $produto->descricao,
                    'imagem_produto' => $produto->imagem,
                ]);

                // Reduzir estoque
                $produto->reduzirEstoque($item['quantidade']);
            }

            Log::info('✅ Pedido criado pelo CheckoutService', [
                'pedido_id' => $pedido->id,
                'numero_pedido' => $pedido->numero_pedido,
                'user_id' => $user->id,
                'total' => $pedido->total
            ]);

            return $pedido;
        });
    }

    /**
     * Formata um valor para moeda brasileira.
     */
    private function formatarMoeda(float $valor): string
    {
        return 'R$ ' . number_format($valor, 2, ',', '.');
    }
}