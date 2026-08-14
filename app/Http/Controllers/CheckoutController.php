<?php
// app/Http/Controllers/CheckoutController.php

namespace App\Http\Controllers;

use App\DTOs\OrderDTO;
use App\Http\Requests\CheckoutRequest;
use App\Models\Pedido;
use App\Services\OrderService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class CheckoutController extends Controller
{
    public function __construct(
        protected OrderService $orderService
    ) {}

    /**
     * Mostra a página de checkout
     */
    public function index(): View|RedirectResponse
    {
        $carrinho = session()->get('carrinho', []);
        
        if (empty($carrinho)) {
            return redirect()->route('carrinho.index')
                ->with('error', 'Seu carrinho está vazio!');
        }

        // Verificar estoque dos produtos
        try {
            foreach ($carrinho as $id => $item) {
                $produto = \App\Models\Produto::find($id);
                if (!$produto || !$produto->temEstoque($item['quantidade'])) {
                    return redirect()->route('carrinho.index')
                        ->with('error', "Produto '{$item['nome']}' não tem estoque suficiente!");
                }
            }
        } catch (\Exception $e) {
            return redirect()->route('carrinho.index')
                ->with('error', 'Erro ao verificar estoque: ' . $e->getMessage());
        }

        // Calcular totais
        $subtotal = $this->calculateSubtotal($carrinho);
        $desconto = $this->calculateDiscount($carrinho);
        $total = $subtotal - $desconto;

        return view('checkout.index', compact('carrinho', 'subtotal', 'desconto', 'total'));
    }

    /**
     * Processa o checkout e cria o pedido
     */
    public function processar(CheckoutRequest $request): RedirectResponse
    {
        $carrinho = session()->get('carrinho', []);
        
        if (empty($carrinho)) {
            return redirect()->route('carrinho.index')
                ->with('error', 'Seu carrinho está vazio!');
        }

        try {
            // Criar DTO
            $dto = OrderDTO::fromRequest($request);

            // Validar método de pagamento
            if (!$dto->isValidPaymentMethod()) {
                return back()->with('error', 'Método de pagamento inválido!');
            }

            // Criar pedido via Service
            $pedido = $this->orderService->createOrder($dto, $carrinho);

            // Limpar carrinho
            session()->forget('carrinho');

            // Redirecionar para sucesso
            return redirect()->route('checkout.sucesso', $pedido)
                ->with('success', 'Pedido realizado com sucesso!');

        } catch (\App\Exceptions\OutOfStockException $e) {
            return back()->with('error', $e->getMessage());
        } catch (\Exception $e) {
            return back()->with('error', 'Erro ao processar pedido: ' . $e->getMessage());
        }
    }

    /**
     * Página de sucesso após pedido
     */
    public function sucesso(Pedido $pedido): View|RedirectResponse
    {
        // Verificar se o pedido pertence ao usuário
        if ($pedido->user_id !== auth()->id()) {
            abort(403);
        }

        $pedido->load('itens');
        return view('checkout.sucesso', compact('pedido'));
    }

    /**
     * Página de pedidos do usuário
     */
    public function meusPedidos(): View
    {
        $pedidos = $this->orderService->getUserOrders(auth()->id(), 10);
        return view('checkout.pedidos', compact('pedidos'));
    }

    /**
     * Detalhes de um pedido específico
     */
    public function detalhes(Pedido $pedido): View|RedirectResponse
    {
        if ($pedido->user_id !== auth()->id()) {
            abort(403);
        }

        $pedido->load('itens');
        return view('checkout.detalhes', compact('pedido'));
    }

    /**
     * Cancelar pedido
     */
    public function cancelar(Pedido $pedido): RedirectResponse
    {
        if ($pedido->user_id !== auth()->id()) {
            abort(403);
        }

        try {
            $this->orderService->cancelOrder($pedido);
            return back()->with('success', 'Pedido cancelado com sucesso!');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    // ===== MÉTODOS PRIVADOS =====

    /**
     * Calcular subtotal do carrinho
     */
    private function calculateSubtotal(array $carrinho): float
    {
        $subtotal = 0;
        foreach ($carrinho as $item) {
            $preco = $item['preco'] ?? $item['preco_unitario'] ?? 0;
            $subtotal += $preco * $item['quantidade'];
        }
        return $subtotal;
    }

    /**
     * Calcular desconto
     */
    private function calculateDiscount(array $carrinho): float
    {
        // Lógica para descontos (frete grátis, cupom, etc)
        return 0;
    }
}