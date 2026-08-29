<?php

namespace App\Http\Controllers;

use App\Models\Pedido;
use App\Models\Produto;
use App\Services\CheckoutService;
use App\Services\PaymentService;
use App\Domain\Pedidos\Enums\StatusPedidoEnum;
use App\Domain\Pedidos\Enums\StatusPagamentoEnum;
use App\Http\Requests\Checkout\ProcessarRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\Log;
use Illuminate\Auth\Access\AuthorizationException;

class CheckoutController extends Controller
{
    /**
     * @var CheckoutService
     */
    protected CheckoutService $checkoutService;

    /**
     * @var PaymentService
     */
    protected PaymentService $paymentService;

    /**
     * Construtor do controller.
     *
     * @param CheckoutService $checkoutService
     * @param PaymentService $paymentService
     */
    public function __construct(
        CheckoutService $checkoutService,
        PaymentService $paymentService
    ) {
        $this->checkoutService = $checkoutService;
        $this->paymentService = $paymentService;
    }

    /**
     * Mostra a página de checkout.
     *
     * @return View|RedirectResponse
     */
    public function index(): View|RedirectResponse
    {
        try {
            $user = auth()->user();

            if (!$user) {
                return redirect()->route('login')
                    ->with('error', 'Faça login para finalizar sua compra.');
            }

            if (!$this->usuarioTemEndereco($user)) {
                return redirect()->route('cliente.perfil.edit')
                    ->with('warning', 'Complete seu endereço antes de finalizar a compra.');
            }

            $carrinho = $this->checkoutService->getCarrinhoCompleto();

            if ($carrinho->isEmpty()) {
                return redirect()->route('carrinho.index')
                    ->with('error', 'Seu carrinho está vazio!');
            }

            $estoqueValido = $this->checkoutService->verificarEstoque($carrinho);
            if (!$estoqueValido['valido']) {
                return redirect()->route('carrinho.index')
                    ->with('error', $estoqueValido['mensagem']);
            }

            $subtotal = $this->checkoutService->calcularSubtotal($carrinho);
            $total = $subtotal;

            Log::info('Checkout iniciado', [
                'user_id' => $user->id,
                'total_items' => $carrinho->count(),
                'total' => $total
            ]);

            return view('checkout.index', compact('carrinho', 'subtotal', 'total'));

        } catch (\Exception $e) {
            Log::error('Erro ao carregar checkout', [
                'erro' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);

            return redirect()->route('carrinho.index')
                ->with('error', 'Erro ao carregar checkout. Tente novamente.');
        }
    }

    /**
     * Processa o checkout e finaliza a compra.
     *
     * @param Request $request
     * @return RedirectResponse
     */
    public function processar(Request $request): RedirectResponse
    {
        $request->validate([
            'endereco_entrega' => 'required|string|max:500',
            'forma_pagamento' => 'required|in:pix,boleto,cartao,cartao_credito,cartao_debito,credito,debito',
        ]);

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

            $estoqueValido = $this->checkoutService->verificarEstoque($carrinho);
            if (!$estoqueValido['valido']) {
                return redirect()->route('carrinho.index')
                    ->with('error', $estoqueValido['mensagem']);
            }

            $pedido = $this->checkoutService->criarPedido($user, $carrinho, $request->forma_pagamento);

            // ✅ VERIFICAÇÃO DE SEGURANÇA
            if (!$pedido || !$pedido->id) {
                Log::error('❌ Pedido não foi criado corretamente', [
                    'user_id' => $user->id
                ]);
                return back()->with('error', 'Erro ao criar pedido. Tente novamente.');
            }

            $result = $this->paymentService->processPayment($pedido, $request->forma_pagamento);

            if ($result->success) {
                Log::info('Pedido criado e pago com sucesso', [
                    'pedido_id' => $pedido->id,
                    'numero_pedido' => $pedido->numero_pedido,
                    'user_id' => $user->id,
                    'total' => $pedido->total,
                    'payment_id' => $result->payment_id ?? null
                ]);

                session()->forget('carrinho');

                // ✅ REDIRECIONAMENTO FUNCIONA LOCAL E PRODUÇÃO
                return redirect()->route('checkout.sucesso', ['pedido' => $pedido->id])
                    ->with('success', 'Pedido realizado com sucesso!');
            }

            Log::warning('Pagamento falhou', [
                'pedido_id' => $pedido->id,
                'user_id' => $user->id,
                'error' => $result->message
            ]);

            return redirect()->route('checkout.falha', ['pedido' => $pedido->id])
                ->with('error', 'Erro ao processar pagamento: ' . $result->message);

        } catch (\Exception $e) {
            Log::error('Erro ao processar pedido', [
                'erro' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => auth()->id()
            ]);

            return back()->with('error', 'Erro ao processar pedido: ' . $e->getMessage());
        }
    }

    /**
     * Página de sucesso do pagamento.
     *
     * @param Pedido $pedido
     * @return View|RedirectResponse
     */
    public function sucesso(Pedido $pedido): View
    {
        $this->authorizePedido($pedido);

        if ($pedido->status !== StatusPedidoEnum::PAGO->value) {
            $pedido->update([
                'status' => StatusPedidoEnum::PAGO->value,
                'status_pagamento' => StatusPagamentoEnum::APROVADO->value,
                'data_pagamento' => now(),
            ]);
        }

        $pedido->load(['itens.produto', 'user']);

        return view('checkout.pagamento.sucesso', compact('pedido'));
    }

    /**
     * Página de falha do pagamento.
     *
     * @param Pedido $pedido
     * @return View
     */
    public function falha(Pedido $pedido): View
    {
        $this->authorizePedido($pedido);
        $pedido->load(['itens.produto']);

        return view('checkout.pagamento.falha', compact('pedido'));
    }

    /**
     * Página de pagamento pendente.
     *
     * @param Pedido $pedido
     * @return View
     */
    public function pendente(Pedido $pedido): View
    {
        $this->authorizePedido($pedido);
        $pedido->load(['itens.produto']);

        return view('checkout.pagamento.pendente', compact('pedido'));
    }

    /**
     * Página de pagamento.
     *
     * @param Pedido $pedido
     * @param string $metodo
     * @return View|RedirectResponse
     */
    public function pagamento(Pedido $pedido, string $metodo): View|RedirectResponse
    {
        $this->authorizePedido($pedido);

        return match ($metodo) {
            'pix'    => $this->pix($pedido),
            'boleto' => $this->boleto($pedido),
            'cartao' => $this->cartao($pedido),
            default  => abort(404, 'Método de pagamento inválido.'),
        };
    }

    /**
     * Status do pagamento.
     *
     * @param Pedido $pedido
     * @param string $status
     * @return View|RedirectResponse
     */
    public function status(Pedido $pedido, string $status): View|RedirectResponse
    {
        $this->authorizePedido($pedido);

        return match ($status) {
            'sucesso' => $this->sucesso($pedido),
            'falha'   => $this->falha($pedido),
            'pendente' => $this->pendente($pedido),
            default   => abort(404, 'Status inválido.'),
        };
    }

    /**
     * Página de PIX.
     *
     * @param Pedido $pedido
     * @return View
     */
    public function pix(Pedido $pedido): View
    {
        $this->authorizePedido($pedido);
        $pixData = $this->paymentService->getPixData($pedido);

        return view('checkout.pagamento.pix', compact('pedido', 'pixData'));
    }

    /**
     * Página de Boleto.
     *
     * @param Pedido $pedido
     * @return View
     */
    public function boleto(Pedido $pedido): View
    {
        $this->authorizePedido($pedido);
        $boletoData = $this->paymentService->getBoletoData($pedido);

        return view('checkout.pagamento.boleto', compact('pedido', 'boletoData'));
    }

    /**
     * Página de Cartão.
     *
     * @param Pedido $pedido
     * @return View
     */
    public function cartao(Pedido $pedido): View
    {
        $this->authorizePedido($pedido);
        $preference = $this->paymentService->getPreference($pedido);

        return view('checkout.pagamento.cartao', compact('pedido', 'preference'));
    }

    /**
     * Lista os pedidos do usuário.
     *
     * @param Request $request
     * @return View|RedirectResponse
     */
    public function meusPedidos(Request $request): View|RedirectResponse
    {
        $user = auth()->user();

        if (!$user) {
            return redirect()->route('login')
                ->with('error', 'Faça login para ver seus pedidos.');
        }

        $pedidos = Pedido::where('user_id', $user->id)
            ->with(['itens.produto'])
            ->orderByDesc('created_at')
            ->paginate($request->get('per_page', 10));

        return view('checkout.pedidos', compact('pedidos'));
    }

    /**
     * Detalhes do pedido.
     *
     * @param Pedido $pedido
     * @return View
     */
    public function detalhes(Pedido $pedido): View
    {
        $this->authorizePedido($pedido);
        $pedido->load(['itens.produto', 'user']);

        return view('checkout.detalhes', compact('pedido'));
    }

    /**
     * Cancela um pedido.
     *
     * @param Pedido $pedido
     * @return RedirectResponse
     */
    public function cancelar(Pedido $pedido): RedirectResponse
    {
        $this->authorizePedido($pedido);

        if (!$pedido->podeCancelar()) {
            return back()->with('error', 'Este pedido não pode ser cancelado.');
        }

        $pedido->update([
            'status' => StatusPedidoEnum::CANCELADO->value,
            'status_pagamento' => StatusPagamentoEnum::CANCELADO->value,
        ]);

        foreach ($pedido->itens as $item) {
            $produto = Produto::find($item->produto_id);
            if ($produto) {
                $produto->quantidade = ($produto->quantidade ?? 0) + $item->quantidade;
                $produto->save();
            }
        }

        Log::info('Pedido cancelado', [
            'pedido_id' => $pedido->id,
            'numero_pedido' => $pedido->numero_pedido,
            'user_id' => auth()->id()
        ]);

        return redirect()->route('cliente.pedidos.index')
            ->with('success', 'Pedido cancelado com sucesso!');
    }

    /**
     * Verifica se o usuário tem endereço completo.
     *
     * @param mixed $user
     * @return bool
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
     * @param Pedido $pedido
     * @throws AuthorizationException
     */
    private function authorizePedido(Pedido $pedido): void
    {
        if (!auth()->check() || $pedido->user_id !== auth()->id()) {
            abort(403, 'Você não tem permissão para acessar este pedido.');
        }
    }
}