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
    ) {
        // 🔒 Garantir que TODOS os métodos exijam login
        $this->middleware('auth')->except(['index', 'processar']);
    }

    /**
     * Mostra a página de checkout
     */
    public function index(): View|RedirectResponse
    {
        // 🔒 VERIFICAR SE O USUÁRIO ESTÁ LOGADO
        if (!auth()->check()) {
            return redirect()->route('login')
                ->with('error', 'Faça login para finalizar sua compra.');
        }

        $carrinho = session()->get('carrinho', []);
        
        if (empty($carrinho)) {
            return redirect()->route('carrinho.index')
                ->with('error', 'Seu carrinho está vazio!');
        }

        // Verificar estoque dos produtos
        try {
            foreach ($carrinho as $id => $item) {
                $produto = Produto::find($id);
                if (!$produto) {
                    return redirect()->route('carrinho.index')
                        ->with('error', "Produto não encontrado!");
                }
                if (!$produto->temEstoque($item['quantidade'])) {
                    return redirect()->route('carrinho.index')
                        ->with('error', "Produto '{$item['nome']}' não tem estoque suficiente! Disponível: {$produto->quantidade}");
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
        // 🔒 VERIFICAR SE O USUÁRIO ESTÁ LOGADO
        if (!auth()->check()) {
            return redirect()->route('login')
                ->with('error', 'Faça login para finalizar sua compra.');
        }

        $carrinho = session()->get('carrinho', []);
        
        if (empty($carrinho)) {
            return redirect()->route('carrinho.index')
                ->with('error', 'Seu carrinho está vazio!');
        }

        try {
            // 🔒 Validar estoque novamente (por segurança)
            foreach ($carrinho as $id => $item) {
                $produto = Produto::find($id);
                if (!$produto || !$produto->temEstoque($item['quantidade'])) {
                    return back()->with('error', "Produto '{$item['nome']}' não tem estoque suficiente!");
                }
            }

            // Criar DTO
            $dto = OrderDTO::fromRequest($request);

            // Validar método de pagamento
            if (!$dto->isValidPaymentMethod()) {
                return back()->with('error', 'Método de pagamento inválido!');
            }

            // Criar pedido via Service
            $pedido = $this->orderService->createOrder($dto, $carrinho);

            // 🔒 Verificar se o pedido foi criado
            if (!$pedido) {
                return back()->with('error', 'Erro ao criar pedido. Tente novamente.');
            }

            // Limpar carrinho
            session()->forget('carrinho');

            // ============================================
            // REDIRECIONAR PARA O MÉTODO DE PAGAMENTO
            // ============================================
            
            switch ($dto->forma_pagamento) {
                case 'pix':
                    $pixData = $this->paymentService->generatePix($pedido);
                    session()->put('pix_data', $pixData);
                    return redirect()->route('checkout.pix', $pedido)
                        ->with('pix_data', $pixData);

                case 'boleto':
                    $boletoData = $this->paymentService->generateBoleto($pedido);
                    session()->put('boleto_data', $boletoData);
                    return redirect()->route('checkout.boleto', $pedido)
                        ->with('boleto_data', $boletoData);

                case 'cartao':
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
                'trace' => $e->getTraceAsString(),
                'user_id' => auth()->id(),
                'carrinho' => $carrinho
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
        // 🔒 Verificar se o usuário está logado
        if (!auth()->check()) {
            return redirect()->route('login')
                ->with('error', 'Faça login para acessar esta página.');
        }

        // 🔒 Verificar se o pedido pertence ao usuário
        if ($pedido->user_id !== auth()->id()) {
            abort(403, 'Você não tem permissão para acessar este pedido.');
        }

        // 🔒 Verificar se o pedido já foi pago
        if ($pedido->status === 'pago' || $pedido->status_pagamento === 'approved') {
            return redirect()->route('checkout.sucesso', $pedido)
                ->with('info', 'Este pedido já foi pago!');
        }

        // 🔒 Verificar se o pedido pode ser pago
        if (!in_array($pedido->status, ['pendente', 'pago'])) {
            return redirect()->route('checkout.detalhes', $pedido)
                ->with('error', 'Este pedido não pode ser pago.');
        }

        $pixData = session()->get('pix_data');
        
        if (!$pixData) {
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
        if (!auth()->check()) {
            return redirect()->route('login')
                ->with('error', 'Faça login para acessar esta página.');
        }

        if ($pedido->user_id !== auth()->id()) {
            abort(403, 'Você não tem permissão para acessar este pedido.');
        }

        if ($pedido->status === 'pago' || $pedido->status_pagamento === 'approved') {
            return redirect()->route('checkout.sucesso', $pedido)
                ->with('info', 'Este pedido já foi pago!');
        }

        if (!in_array($pedido->status, ['pendente', 'pago'])) {
            return redirect()->route('checkout.detalhes', $pedido)
                ->with('error', 'Este pedido não pode ser pago.');
        }

        $boletoData = session()->get('boleto_data');
        
        if (!$boletoData) {
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
        if (!auth()->check()) {
            return redirect()->route('login')
                ->with('error', 'Faça login para acessar esta página.');
        }

        if ($pedido->user_id !== auth()->id()) {
            abort(403, 'Você não tem permissão para acessar este pedido.');
        }

        if ($pedido->status === 'pago' || $pedido->status_pagamento === 'approved') {
            return redirect()->route('checkout.sucesso', $pedido)
                ->with('info', 'Este pedido já foi pago!');
        }

        if (!in_array($pedido->status, ['pendente', 'pago'])) {
            return redirect()->route('checkout.detalhes', $pedido)
                ->with('error', 'Este pedido não pode ser pago.');
        }

        $preference = session()->get('preference');
        
        if (!$preference) {
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
        if (!auth()->check()) {
            return redirect()->route('login')
                ->with('error', 'Faça login para acessar esta página.');
        }

        if ($pedido->user_id !== auth()->id()) {
            abort(403, 'Você não tem permissão para acessar este pedido.');
        }

        // Verificar status do pagamento
        if ($pedido->status_pagamento !== 'pago' && $pedido->status !== 'pago') {
            // Verificar status no gateway
            try {
                $status = $this->paymentService->checkPaymentStatus($pedido);
                
                if ($status === 'approved' || $status === 'pago') {
                    $pedido->status = 'pago';
                    $pedido->status_pagamento = 'approved';
                    $pedido->data_pagamento = now();
                    $pedido->save();
                } else {
                    return redirect()->route('checkout.pendente', $pedido)
                        ->with('warning', 'Seu pagamento ainda não foi confirmado. Aguarde alguns minutos.');
                }
            } catch (\Exception $e) {
                \Log::error('Erro ao verificar status do pagamento: ' . $e->getMessage());
                return redirect()->route('checkout.pendente', $pedido)
                    ->with('warning', 'Não foi possível confirmar seu pagamento. Verifique em alguns minutos.');
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
        if (!auth()->check()) {
            return redirect()->route('login')
                ->with('error', 'Faça login para acessar esta página.');
        }

        if ($pedido->user_id !== auth()->id()) {
            abort(403, 'Você não tem permissão para acessar este pedido.');
        }

        $error = session()->get('error', 'Ocorreu um erro ao processar seu pagamento. Tente novamente.');
        
        return view('checkout.pagamento.falha', compact('pedido', 'error'));
    }

    /**
     * Página de pagamento pendente
     */
    public function pendente(Pedido $pedido): View|RedirectResponse
    {
        if (!auth()->check()) {
            return redirect()->route('login')
                ->with('error', 'Faça login para acessar esta página.');
        }

        if ($pedido->user_id !== auth()->id()) {
            abort(403, 'Você não tem permissão para acessar este pedido.');
        }

        if ($pedido->status === 'pago' || $pedido->status_pagamento === 'approved') {
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
    public function meusPedidos(): View|RedirectResponse
    {
        if (!auth()->check()) {
            return redirect()->route('login')
                ->with('error', 'Faça login para ver seus pedidos.');
        }

        $pedidos = $this->orderService->getUserOrders(auth()->id(), 10);
        return view('checkout.pedidos', compact('pedidos'));
    }

    /**
     * Detalhes de um pedido específico
     */
    public function detalhes(Pedido $pedido): View|RedirectResponse
    {
        if (!auth()->check()) {
            return redirect()->route('login')
                ->with('error', 'Faça login para acessar esta página.');
        }

        if ($pedido->user_id !== auth()->id()) {
            abort(403, 'Você não tem permissão para acessar este pedido.');
        }

        $pedido->load(['itens', 'user']);
        return view('checkout.detalhes', compact('pedido'));
    }

    /**
     * Cancelar pedido
     */
    public function cancelar(Pedido $pedido): RedirectResponse
    {
        if (!auth()->check()) {
            return redirect()->route('login')
                ->with('error', 'Faça login para acessar esta página.');
        }

        if ($pedido->user_id !== auth()->id()) {
            abort(403, 'Você não tem permissão para acessar este pedido.');
        }

        // 🔒 Verificar se o pedido pode ser cancelado
        if (!$pedido->podeCancelar()) {
            return back()->with('error', 'Este pedido não pode ser cancelado. Status atual: ' . $pedido->status_label);
        }

        try {
            $this->orderService->cancelOrder($pedido);
            return back()->with('success', 'Pedido cancelado com sucesso!');
        } catch (\Exception $e) {
            \Log::error('Erro ao cancelar pedido: ' . $e->getMessage(), [
                'pedido_id' => $pedido->id,
                'user_id' => auth()->id()
            ]);
            return back()->with('error', 'Erro ao cancelar pedido: ' . $e->getMessage());
        }
    }

    /**
     * Reembolsar pedido (Admin)
     */
    public function reembolsar(Pedido $pedido): RedirectResponse
    {
        if (!auth()->check() || !auth()->user()->isAdmin()) {
            abort(403, 'Apenas administradores podem reembolsar pedidos.');
        }

        try {
            $this->orderService->refundOrder($pedido);
            return back()->with('success', 'Pedido reembolsado com sucesso!');
        } catch (\Exception $e) {
            return back()->with('error', 'Erro ao reembolsar pedido: ' . $e->getMessage());
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
        return round($subtotal, 2);
    }

    /**
     * Calcular desconto
     */
    private function calculateDiscount(array $carrinho): float
    {
        // Lógica para descontos (frete grátis, cupom, etc)
        // Exemplo: frete grátis para compras acima de R$ 100
        $subtotal = $this->calculateSubtotal($carrinho);
        
        if ($subtotal >= 100) {
            return 0; // Frete grátis
        }
        
        return 0;
    }

    /**
     * Calcular frete
     */
    private function calculateShipping(array $carrinho): float
    {
        $subtotal = $this->calculateSubtotal($carrinho);
        
        // Frete grátis acima de R$ 100
        if ($subtotal >= 100) {
            return 0;
        }
        
        // Frete fixo para compras menores
        return 15.00;
    }
}