<?php

namespace App\Http\Controllers;

use App\Models\Pedido;
use App\Models\PedidoItem;
use App\Models\Produto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CheckoutController extends Controller
{
    /**
     * Mostra a página de checkout
     */
    public function index()
    {
        $carrinho = session()->get('carrinho', []);
        
        if (empty($carrinho)) {
            return redirect()->route('carrinho.index')
                ->with('error', 'Seu carrinho está vazio!');
        }

        // Verificar estoque
        foreach ($carrinho as $id => $item) {
            $produto = Produto::find($id);
            if (!$produto || $produto->estoque < $item['quantidade']) {
                return redirect()->route('carrinho.index')
                    ->with('error', "Produto {$item['nome']} não tem estoque suficiente!");
            }
        }

        $subtotal = $this->calcularSubtotal($carrinho);
        $desconto = 0;
        $total = $subtotal - $desconto;

        return view('checkout.index', compact('carrinho', 'subtotal', 'desconto', 'total'));
    }

    /**
     * Processa o checkout e cria o pedido
     */
    public function processar(Request $request)
    {
        $request->validate([
            'endereco' => 'required|string|min:10',
            'cidade' => 'required|string|min:3',
            'estado' => 'required|string|size:2',
            'cep' => 'required|string|min:8',
            'forma_pagamento' => 'required|in:cartao,boleto,pix',
            'observacoes' => 'nullable|string|max:500'
        ]);

        $carrinho = session()->get('carrinho', []);
        
        if (empty($carrinho)) {
            return redirect()->route('carrinho.index')
                ->with('error', 'Carrinho vazio!');
        }

        try {
            DB::beginTransaction();

            // Calcular valores
            $subtotal = $this->calcularSubtotal($carrinho);
            $desconto = $this->calcularDesconto($carrinho);
            $total = $subtotal - $desconto;

            // Criar pedido
            $pedido = Pedido::create([
                'user_id' => Auth::id(),
                'numero_pedido' => Pedido::gerarNumeroPedido(),
                'subtotal' => $subtotal,
                'desconto' => $desconto,
                'total' => $total,
                'status' => 'pendente',
                'forma_pagamento' => $request->forma_pagamento,
                'status_pagamento' => 'aguardando',
                'observacoes' => $request->observacoes,
                'endereco_entrega' => $request->endereco,
                'cidade' => $request->cidade,
                'estado' => $request->estado,
                'cep' => $request->cep
            ]);

            // Criar itens do pedido
            foreach ($carrinho as $id => $item) {
                $produto = Produto::find($id);
                
                if (!$produto) {
                    throw new \Exception("Produto não encontrado: {$id}");
                }

                // Verificar estoque
                if ($produto->estoque < $item['quantidade']) {
                    throw new \Exception("Estoque insuficiente para: {$produto->nome}");
                }

                // Criar item
                PedidoItem::create([
                    'pedido_id' => $pedido->id,
                    'produto_id' => $id,
                    'quantidade' => $item['quantidade'],
                    'preco_unitario' => $produto->preco,
                    'preco_promocional' => $produto->preco_promocional,
                    'subtotal' => $item['quantidade'] * $item['preco'],
                    'nome_produto' => $produto->nome,
                    'imagem_produto' => $produto->imagem
                ]);

                // Atualizar estoque
                $produto->decrement('estoque', $item['quantidade']);
            }

            // Limpar carrinho
            session()->forget('carrinho');

            DB::commit();

            // Redirecionar para página de sucesso
            return redirect()->route('checkout.sucesso', $pedido)
                ->with('success', 'Pedido realizado com sucesso!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Erro ao processar pedido: ' . $e->getMessage());
        }
    }

    /**
     * Página de sucesso após pedido
     */
    public function sucesso(Pedido $pedido)
    {
        // Verificar se o pedido pertence ao usuário
        if ($pedido->user_id !== Auth::id()) {
            abort(403);
        }

        return view('checkout.sucesso', compact('pedido'));
    }

    /**
     * Página de pedidos do usuário
     */
    public function meusPedidos()
    {
        $pedidos = Pedido::where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('checkout.pedidos', compact('pedidos'));
    }

    /**
     * Detalhes de um pedido específico
     */
    public function detalhes(Pedido $pedido)
    {
        if ($pedido->user_id !== Auth::id()) {
            abort(403);
        }

        return view('checkout.detalhes', compact('pedido'));
    }

    /**
     * Cancelar pedido
     */
    public function cancelar(Pedido $pedido)
    {
        if ($pedido->user_id !== Auth::id()) {
            abort(403);
        }

        if (!$pedido->podeCancelar()) {
            return back()->with('error', 'Este pedido não pode ser cancelado!');
        }

        try {
            DB::beginTransaction();

            // Restaurar estoque
            foreach ($pedido->itens as $item) {
                $produto = Produto::find($item->produto_id);
                if ($produto) {
                    $produto->increment('estoque', $item->quantidade);
                }
            }

            $pedido->update(['status' => 'cancelado']);

            DB::commit();

            return back()->with('success', 'Pedido cancelado com sucesso!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Erro ao cancelar pedido!');
        }
    }

    // ===== MÉTODOS PRIVADOS =====

    private function calcularSubtotal(array $carrinho): float
    {
        $subtotal = 0;
        foreach ($carrinho as $item) {
            $subtotal += $item['preco'] * $item['quantidade'];
        }
        return $subtotal;
    }

    private function calcularDesconto(array $carrinho): float
    {
        // Lógica para calcular descontos (ex: frete grátis, cupom, etc)
        // Por enquanto sem desconto
        return 0;
    }
}