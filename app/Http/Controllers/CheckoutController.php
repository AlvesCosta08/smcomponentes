<?php
// app/Http/Controllers/CheckoutController.php

namespace App\Http\Controllers;

use App\DTOs\OrderDTO;
use App\Http\Requests\CheckoutRequest;
use App\Models\Pedido;
use App\Models\Produto;
use App\Services\OrderService;
use App\Services\PaymentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CheckoutController extends Controller
{
    public function __construct(
        protected OrderService $orderService,
        protected PaymentService $paymentService
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
                $produto = Produto::find($id);
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

            // ============================================
            // REDIRECIONAR PARA O MÉTODO DE PAGAMENTO
            // ============================================
            
            switch ($dto->forma_pagamento) {
                case 'pix':
                    // Gerar PIX
                    $pixData = $this->paymentService->generatePix($pedido);
                    session()->put('pix_data', $pixData);
                    return redirect()->route('checkout.pix', $pedido)
                        ->with('pix_data', $pixData);

                case 'boleto':
                    // Gerar Boleto
                    $boletoData = $this->paymentService->generateBoleto($pedido);
                    session()->put('boleto_data', $boletoData);
                    return redirect()->route('checkout.boleto', $pedido)
                        ->with('boleto_data', $boletoData);

                case 'cartao':
                    // Criar preferência para Cartão
                    $preference = $this->paymentService->createPreference($pedido);
                    session()->put('preference', $preference);
                    return redirect()->route('checkout.cartao', $pedido)
                        ->with('preference', $preference);

                default:
                    return redirect()->route('checkout.sucesso', $pedido)
                        ->with('success', 'Pedido realizado com sucesso!');
            }

        } catch (\App\Exceptions\OutOfStockException $e) {
            return back()->with('error', $e->getMessage());
        } catch (\Exception $e) {
            \Log::error('Erro ao processar pedido: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return back()->with('error', 'Erro ao processar pedido: ' . $e->getMessage());
        }
    }

    // ============================================
    // MÉTODOS DE PAGAMENTO
    // ============================================

    /**
     * Página de pagamento PIX
     */
    public function pix(Pedido $pedido): View|RedirectResponse
    {
        // Verificar se o pedido pertence ao usuário
        if ($pedido->user_id !== auth()->id()) {
            abort(403);
        }

        // Verificar se o pedido já foi pago
        if ($pedido->status === 'pago') {
            return redirect()->route('checkout.sucesso', $pedido)
                ->with('info', 'Este pedido já foi pago!');
        }

        // Buscar dados do PIX
        $pixData = session()->get('pix_data');
        
        if (!$pixData) {
            // Gerar novamente se não estiver na sessão
            $pixData = $this->paymentService->generatePix($pedido);
            session()->put('pix_data', $pixData);
        }

        return view('checkout.pagamento.pix', compact('pedido', 'pixData'));
    }

    /**
     * Página de pagamento Boleto
     */
    public function boleto(Pedido $pedido): View|RedirectResponse
    {
        // Verificar se o pedido pertence ao usuário
        if ($pedido->user_id !== auth()->id()) {
            abort(403);
        }

        // Verificar se o pedido já foi pago
        if ($pedido->status === 'pago') {
            return redirect()->route('checkout.sucesso', $pedido)
                ->with('info', 'Este pedido já foi pago!');
        }

        // Buscar dados do Boleto
        $boletoData = session()->get('boleto_data');
        
        if (!$boletoData) {
            // Gerar novamente se não estiver na sessão
            $boletoData = $this->paymentService->generateBoleto($pedido);
            session()->put('boleto_data', $boletoData);
        }

        return view('checkout.pagamento.boleto', compact('pedido', 'boletoData'));
    }

    /**
     * Página de pagamento Cartão de Crédito
     */
    public function cartao(Pedido $pedido): View|RedirectResponse
    {
        // Verificar se o pedido pertence ao usuário
        if ($pedido->user_id !== auth()->id()) {
            abort(403);
        }

        // Verificar se o pedido já foi pago
        if ($pedido->status === 'pago') {
            return redirect()->route('checkout.sucesso', $pedido)
                ->with('info', 'Este pedido já foi pago!');
        }

        // Buscar preferência do Mercado Pago
        $preference = session()->get('preference');
        
        if (!$preference) {
            // Criar preferência novamente se não estiver na sessão
            $preference = $this->paymentService->createPreference($pedido);
            session()->put('preference', $preference);
        }

        return view('checkout.pagamento.cartao', compact('pedido', 'preference'));
    }

    // ============================================
    // STATUS DO PAGAMENTO
    // ============================================

    /**
     * Página de sucesso após pagamento
     */
    public function sucesso(Pedido $pedido): View|RedirectResponse
    {
        // Verificar se o pedido pertence ao usuário
        if ($pedido->user_id !== auth()->id()) {
            abort(403);
        }

        // Verificar status do pagamento
        if ($pedido->status_pagamento !== 'pago' && $pedido->status !== 'pago') {
            // Verificar status no gateway
            $status = $this->paymentService->checkPaymentStatus($pedido);
            
            if ($status === 'approved') {
                $pedido->status = 'pago';
                $pedido->status_pagamento = 'approved';
                $pedido->data_pagamento = now();
                $pedido->save();
            } else {
                // Se não estiver pago, redirecionar para pendente
                return redirect()->route('checkout.pendente', $pedido)
                    ->with('warning', 'Seu pagamento ainda não foi confirmado.');
            }
        }

        $pedido->load('itens');
        return view('checkout.pagamento.sucesso', compact('pedido'));
    }

    /**
     * Página de falha no pagamento
     */
    public function falha(Pedido $pedido): View|RedirectResponse
    {
        // Verificar se o pedido pertence ao usuário
        if ($pedido->user_id !== auth()->id()) {
            abort(403);
        }

        $error = session()->get('error', 'Ocorreu um erro ao processar seu pagamento.');
        
        return view('checkout.pagamento.falha', compact('pedido', 'error'));
    }

    /**
     * Página de pagamento pendente
     */
    public function pendente(Pedido $pedido): View|RedirectResponse
    {
        // Verificar se o pedido pertence ao usuário
        if ($pedido->user_id !== auth()->id()) {
            abort(403);
        }

        // Verificar se o pedido já foi pago
        if ($pedido->status === 'pago') {
            return redirect()->route('checkout.sucesso', $pedido)
                ->with('info', 'Seu pagamento foi confirmado!');
        }

        return view('checkout.pagamento.pendente', compact('pedido'));
    }

    // ============================================
    // PEDIDOS DO USUÁRIO
    // ============================================

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

    // ============================================
    // MÉTODOS PRIVADOS
    // ============================================

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