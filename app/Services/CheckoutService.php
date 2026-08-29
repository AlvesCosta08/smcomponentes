<?php

namespace App\Services;

use App\Models\Pedido;
use App\Models\Produto;
use App\Models\User;
use App\Domain\Pedidos\Enums\StatusPedidoEnum;
use App\Domain\Pedidos\Enums\StatusPagamentoEnum;
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

            // ✅ CORRIGIDO DEFINITIVO: USAR SOMENTE valor_atacado
            $preco = $produto->valor_atacado ?? 0;
            $quantidade = $item['quantidade'] ?? 1;

            Log::info('🔍 Preço do produto', [
                'produto_id' => $produto->id,
                'valor_atacado' => $produto->valor_atacado,
                'valor_unitario' => $produto->valor_unitario,
                'preco_usado' => $preco,
                'quantidade' => $quantidade
            ]);

            $resultado->push([
                'produto_id' => $produto->id,
                'nome' => $produto->descricao ?? 'Produto',
                'slug' => $produto->slug ?? 'produto-' . $produto->id,
                'quantidade' => $quantidade,
                'preco' => $preco,
                'preco_formatado' => $this->formatarMoeda($preco),
                'subtotal' => $preco * $quantidade,
                'subtotal_formatado' => $this->formatarMoeda($preco * $quantidade),
                'estoque' => $produto->quantidade ?? 0,
                'imagem' => $produto->imagem ?? null,
                'disponivel' => ($produto->quantidade ?? 0) > 0,
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

            $quantidade = $produto->quantidade ?? 0;
            $solicitada = $item['quantidade'] ?? 0;

            if ($quantidade < $solicitada) {
                return [
                    'valido' => false,
                    'mensagem' => "Produto '{$produto->descricao}' não tem estoque suficiente. Disponível: {$quantidade}"
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

            Log::info('📊 Criando pedido', [
                'user_id' => $user->id,
                'subtotal' => $subtotal,
                'quantidade_itens' => $carrinho->count()
            ]);

            $pedido = Pedido::create([
                'user_id' => $user->id,
                'numero_pedido' => Pedido::gerarNumeroPedido(),
                'subtotal' => $subtotal,
                'total' => $subtotal,
                'status' => StatusPedidoEnum::PENDENTE->value,
                'status_pagamento' => StatusPagamentoEnum::AGUARDANDO->value,
                'forma_pagamento' => $formaPagamento,
            ]);

            foreach ($carrinho as $item) {
                $produto = Produto::find($item['produto_id']);
                // ✅ CORRIGIDO DEFINITIVO: USAR SOMENTE valor_atacado
                $preco = $produto->valor_atacado ?? 0;
                $quantidade = $item['quantidade'] ?? 1;

                Log::info('📦 Item do pedido', [
                    'produto_id' => $produto->id,
                    'valor_atacado' => $produto->valor_atacado,
                    'preco_usado' => $preco,
                    'quantidade' => $quantidade,
                    'subtotal_item' => $preco * $quantidade
                ]);

                $pedido->itens()->create([
                    'produto_id' => $item['produto_id'],
                    'quantidade' => $quantidade,
                    'preco_unitario' => $preco,
                    'subtotal' => $preco * $quantidade,
                    'nome_produto' => $produto->descricao ?? 'Produto',
                    'imagem_produto' => $produto->imagem ?? null,
                ]);

                // Reduzir estoque
                if (method_exists($produto, 'reduzirEstoque')) {
                    $produto->reduzirEstoque($quantidade);
                } else {
                    $produto->quantidade = ($produto->quantidade ?? 0) - $quantidade;
                    $produto->save();
                }
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