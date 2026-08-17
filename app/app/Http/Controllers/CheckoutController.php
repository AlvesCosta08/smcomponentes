<?php

namespace App\Http\Controllers;

use App\Models\Pedido;
use App\Models\Produto;
use App\Services\CheckoutService;
use App\Services\PaymentService;
use App\Http\Requests\Checkout\ProcessarRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\Log;

class CheckoutController extends Controller
{
    public function __construct(
        protected CheckoutService $checkoutService,
        protected PaymentService $paymentService
    ) {}

    /**
     * Mostra a página de checkout.
     */
    public function index(): View|RedirectResponse
    {
        try {
            $user = auth()->user();

            if (!$user) {
                return redirect()->route('login')
                    ->with('error', 'Faça login para finalizar sua compra.');
            }

            // Verificar se o usuário tem endereço
            if (!$this->usuarioTemEndereco($user)) {
                return redirect()->route('cliente.perfil.edit')
                    ->with('warning', 'Complete seu endereço antes de finalizar a compra.');
            }

            $carrinho = $this->checkoutService->getCarrinhoCompleto();

            if ($carrinho->isEmpty()) {
                return redirect()->route('carrinho.index')
                    ->with('error', 'Seu carrinho está vazio!');
            }

            // Verificar estoque antes de prosseguir
            $estoqueValido = $this->checkoutService->verificarEstoque($carrinho);
            if (!$estoqueValido['valido']) {
                return redirect()->route('carrinho.index')
                    ->with('error', $estoqueValido['mensagem']);
            }

            $subtotal = $this->checkoutService->calcularSubtotal($carrinho);
            $total = $subtotal;

            Log::info('📦 Checkout iniciado', [
                'user_id' => $user->id,
                'total_items' => $carrinho->count(),
                'total' => $total
            ]);

            return view('checkout.index', compact('carrinho', 'subtotal', 'total'));
        } catch (\Exception $e) {
            Log::error('❌ Erro ao carregar checkout', [
                'erro' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);

            return redirect()->route('carrinho.index')
                ->with('error', 'Erro ao carregar checkout. Tente novamente.');
        }
    }

    /**
     * Processa o checkout e finaliza a compra.
     */
    public function processar(ProcessarRequest $request): RedirectResponse
    {
        try {
            $user = auth()->user();

            if (!$user) {
                return redirect()->route('login')
                    ->with('error', 'Faça login para finalizar sua compra.');
            }

            $carrinho = $this->checkoutService->getCarrinhoCompleto();

            if ($carrinho->isEmpty()) {
                return redirect()->route('carrinho.index')
                    ->with('error', 'Seu carrinho está vazio!');
            }

            // Verificar estoque novamente (pode ter mudado)
            $estoqueValido = $this->checkoutService->verificarEstoque($carrinho);
            if (!$estoqueValido['valido']) {
                return redirect()->route('carrinho.index')
                    ->with('error', $estoqueValido['mensagem']);
            }

            // Criar pedido
            $pedido = $this->checkoutService->criarPedido($user, $carrinho, $request->forma_pagamento);

            // Processar pagamento
            $result = $this->paymentService->processPayment($pedido, $request->forma_pagamento);

            if ($result->success) {
                Log::info('✅ Pedido criado e pago com sucesso', [
                    'pedido_id' => $pedido->id,
                    'numero_pedido' => $pedido->numero_pedido,
                    'user_id' => $user->id,
                    'total' => $pedido->total,
                    'payment_id' => $result->payment_id ?? null
                ]);

                // Limpar carrinho
                session()->forget('carrinho');

                return redirect()->route('checkout.sucesso', $pedido)
                    ->with('success', 'Pedido realizado com sucesso!');
            }

            Log::warning('⚠️ Pagamento falhou', [
                'pedido_id' => $pedido->id,
                'user_id' => $user->id,
                'error' => $result->message
            ]);

            return redirect()->route('checkout.falha', $pedido)
                ->with('error', 'Erro ao processar pagamento: ' . $result->message);
        } catch (\Exception $e) {
            Log::error('❌ Erro ao processar pedido', [
                'erro' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => auth()->id()
            ]);

            return back()->with('error', 'Erro ao processar pedido: ' . $e->getMessage());
        }
    }

    /**
     * Método unificado para pagamento.
     */
    public function pagamento(Pedido $pedido, string $metodo): View|RedirectResponse
    {
        $this->authorizePedido($pedido);

        switch ($metodo) {
            case 'pix':
                return $this->pix($pedido);
            case 'boleto':
                return $this->boleto($pedido);
            case 'cartao':
                return $this->cartao($pedido);
            default:
                abort(404, 'Método de pagamento inválido.');
        }
    }

    /**
     * Método unificado para status.
     */
    public function status(Pedido $pedido, string $status): View|RedirectResponse
    {
        $this->authorizePedido($pedido);

        return match ($status) {
            'sucesso' => $this->sucesso($pedido),
            'falha' => $this->falha($pedido),
            'pendente' => $this->pendente($pedido),
            default => abort(404, 'Status inválido.'),
        };
    }

    /**
     * Página de sucesso.
     */
    public function sucesso(Pedido $pedido): View
    {
        $this->authorizePedido($pedido);

        // Garantir que o pedido está como pago
        if ($pedido->status !== 'pago') {
            $pedido->update([
                'status' => 'pago',
                'status_pagamento' => 'approved',
                'data_pagamento' => now(),
            ]);
        }

        $pedido->load('itens', 'itens.produto');

        return view('checkout.pagamento.sucesso', compact('pedido'));
    }

    /**
     * Página de falha.
     */
    public function falha(Pedido $pedido): View
    {
        $this->authorizePedido($pedido);
        $pedido->load('itens');

        return view('checkout.pagamento.falha', compact('pedido'));
    }

    /**
     * Página de pendente.
     */
    public function pendente(Pedido $pedido): View
    {
        $this->authorizePedido($pedido);
        $pedido->load('itens');

        return view('checkout.pagamento.pendente', compact('pedido'));
    }

    /**
     * Página de PIX.
     */
    public function pix(Pedido $pedido): View
    {
        $this->authorizePedido($pedido);

        // Buscar dados do PIX do serviço
        $pixData = $this->paymentService->getPixData($pedido);

        return view('checkout.pagamento.pix', compact('pedido', 'pixData'));
    }

    /**
     * Página de Boleto.
     */
    public function boleto(Pedido $pedido): View
    {
        $this->authorizePedido($pedido);

        $boletoData = $this->paymentService->getBoletoData($pedido);

        return view('checkout.pagamento.boleto', compact('pedido', 'boletoData'));
    }

    /**
     * Página de Cartão.
     */
    public function cartao(Pedido $pedido): View
    {
        $this->authorizePedido($pedido);

        $preference = $this->paymentService->getPreference($pedido);

        return view('checkout.pagamento.cartao', compact('pedido', 'preference'));
    }

    /**
     * Meus pedidos.
     */
    public function meusPedidos(Request $request): View|RedirectResponse
    {
        $user = auth()->user();

        if (!$user) {
            return redirect()->route('login')
                ->with('error', 'Faça login para ver seus pedidos.');
        }

        $pedidos = Pedido::where('user_id', $user->id)
            ->with(['itens', 'itens.produto'])
            ->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 10));

        return view('checkout.pedidos', compact('pedidos'));
    }

    /**
     * Detalhes do pedido.
     */
    public function detalhes(Pedido $pedido): View
    {
        $this->authorizePedido($pedido);

        $pedido->load(['itens', 'itens.produto', 'user']);

        return view('checkout.detalhes', compact('pedido'));
    }

    /**
     * Cancelar pedido.
     */
    public function cancelar(Pedido $pedido): RedirectResponse
    {
        $this->authorizePedido($pedido);

        if (!$pedido->podeCancelar()) {
            return back()->with('error', 'Este pedido não pode ser cancelado.');
        }

        $pedido->update([
            'status' => 'cancelado',
            'status_pagamento' => 'cancelled',
        ]);

        // Restaurar estoque
        foreach ($pedido->itens as $item) {
            $produto = Produto::find($item->produto_id);
            if ($produto) {
                $produto->aumentarEstoque($item->quantidade);
            }
        }

        Log::info('🗑️ Pedido cancelado', [
            'pedido_id' => $pedido->id,
            'numero_pedido' => $pedido->numero_pedido,
            'user_id' => auth()->id()
        ]);

        return back()->with('success', 'Pedido cancelado com sucesso!');
    }

    // ================================================================
    // MÉTODOS PRIVADOS
    // ================================================================

    /**
     * Verifica se o usuário tem endereço completo.
     */
    private function usuarioTemEndereco($user): bool
    {
        return !empty($user->cep) 
            && !empty($user->logradouro) 
            && !empty($user->numero)
            && !empty($user->cidade)
            && !empty($user->estado);
    }

    /**
     * Autoriza o usuário a acessar o pedido.
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    private function authorizePedido(Pedido $pedido): void
    {
        if (!auth()->check() || $pedido->user_id !== auth()->id()) {
            abort(403, 'Você não tem permissão para acessar este pedido.');
        }
    }
}