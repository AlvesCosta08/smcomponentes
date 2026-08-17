<?php

namespace App\Http\Controllers;

use App\Models\Produto;
use App\Http\Requests\Carrinho\AdicionarRequest;
use App\Http\Requests\Carrinho\AtualizarRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;

class CarrinhoController extends Controller
{
    /**
     * Limites do carrinho
     */
    private const MAX_ITEMS = 50;
    private const MAX_QUANTITY_PER_ITEM = 999;

    /**
     * Exibe o carrinho de compras.
     */
    public function index(): View
    {
        try {
            $carrinho = $this->getCarrinhoCompleto();
            $total = $this->calcularTotal($carrinho);
            $totalItems = $this->contarItens($carrinho);

            return view('carrinho.index', [
                'carrinho' => $carrinho,
                'total' => $total,
                'totalFormatado' => $this->formatarMoeda($total),
                'totalItems' => $totalItems,
            ]);
        } catch (\Exception $e) {
            Log::error('❌ Erro ao carregar carrinho', [
                'erro' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return view('carrinho.index', [
                'carrinho' => [],
                'total' => 0,
                'totalFormatado' => 'R$ 0,00',
                'totalItems' => 0
            ])->with('error', 'Erro ao carregar o carrinho. Tente novamente.');
        }
    }

    /**
     * Adiciona um produto ao carrinho.
     */
    public function adicionar(AdicionarRequest $request): JsonResponse|RedirectResponse
    {
        try {
            $produto = Produto::find($request->produto_id);

            if (!$produto) {
                return $this->jsonOrBack($request, false, 'Produto não encontrado!');
            }

            if (!$produto->isDisponivel()) {
                return $this->jsonOrBack($request, false, 'Produto indisponível!');
            }

            if ($request->quantidade > $produto->quantidade) {
                return $this->jsonOrBack(
                    $request,
                    false,
                    "Quantidade indisponível em estoque! Disponível: {$produto->quantidade}"
                );
            }

            $carrinho = $this->getCarrinho();

            // Verificar limite de itens
            if (count($carrinho) >= self::MAX_ITEMS) {
                return $this->jsonOrBack(
                    $request,
                    false,
                    "Carrinho cheio! Limite de " . self::MAX_ITEMS . " itens diferentes."
                );
            }

            // Adicionar ou atualizar item
            $carrinho = $this->adicionarItemCarrinho($carrinho, $produto->id, $request->quantidade, $produto->quantidade);
            $this->salvarCarrinho($carrinho);

            Log::info('🛒 Produto adicionado ao carrinho', [
                'produto_id' => $request->produto_id,
                'quantidade' => $request->quantidade,
                'total_items' => $this->contarItens($carrinho)
            ]);

            return $this->jsonOrBack($request, true, 'Produto adicionado ao carrinho!', [
                'count' => $this->contarItens($carrinho)
            ]);
        } catch (\Exception $e) {
            Log::error('❌ Erro ao adicionar produto ao carrinho', [
                'erro' => $e->getMessage(),
                'produto_id' => $request->produto_id ?? null
            ]);

            return $this->jsonOrBack($request, false, 'Erro ao adicionar produto. Tente novamente.');
        }
    }

    /**
     * Remove um item do carrinho.
     */
    public function remover(Request $request, int $index): RedirectResponse
    {
        try {
            $carrinho = $this->getCarrinho();

            if (!isset($carrinho[$index])) {
                return back()->with('error', 'Item não encontrado!');
            }

            $produtoId = $carrinho[$index]['produto_id'] ?? 'desconhecido';
            unset($carrinho[$index]);
            
            $this->salvarCarrinho(array_values($carrinho));

            Log::info('🗑️ Item removido do carrinho', [
                'index' => $index,
                'produto_id' => $produtoId
            ]);

            return back()->with('success', 'Item removido do carrinho!');
        } catch (\Exception $e) {
            Log::error('❌ Erro ao remover item do carrinho', [
                'erro' => $e->getMessage()
            ]);

            return back()->with('error', 'Erro ao remover item. Tente novamente.');
        }
    }

    /**
     * Atualiza a quantidade de um item no carrinho.
     */
    public function atualizar(AtualizarRequest $request, int $index): JsonResponse|RedirectResponse
    {
        try {
            $carrinho = $this->getCarrinho();

            if (!isset($carrinho[$index])) {
                return $this->jsonOrBack($request, false, 'Item não encontrado!');
            }

            $produto = Produto::find($carrinho[$index]['produto_id']);

            if (!$produto) {
                unset($carrinho[$index]);
                $this->salvarCarrinho(array_values($carrinho));
                return $this->jsonOrBack($request, false, 'Produto não encontrado!');
            }

            if ($request->quantidade > $produto->quantidade) {
                return $this->jsonOrBack(
                    $request,
                    false,
                    "Quantidade indisponível! Estoque: {$produto->quantidade}"
                );
            }

            $carrinho[$index]['quantidade'] = $request->quantidade;
            $this->salvarCarrinho($carrinho);

            Log::info('🔄 Carrinho atualizado', [
                'index' => $index,
                'nova_quantidade' => $request->quantidade,
                'produto_id' => $produto->id
            ]);

            if ($request->ajax() || $request->wantsJson()) {
                $itemTotal = $request->quantidade * $produto->valor_unitario;
                return response()->json([
                    'success' => true,
                    'message' => 'Carrinho atualizado!',
                    'count' => $this->contarItens($carrinho),
                    'item_total' => $itemTotal,
                    'item_total_formatado' => $this->formatarMoeda($itemTotal)
                ]);
            }

            return back()->with('success', 'Carrinho atualizado!');
        } catch (\Exception $e) {
            Log::error('❌ Erro ao atualizar carrinho', [
                'erro' => $e->getMessage()
            ]);

            return $this->jsonOrBack($request, false, 'Erro ao atualizar carrinho. Tente novamente.');
        }
    }

    /**
     * Limpa todo o carrinho.
     */
    public function limpar(Request $request): JsonResponse|RedirectResponse
    {
        try {
            Session::forget('carrinho');

            Log::info('🧹 Carrinho limpo', [
                'user_id' => auth()->id(),
                'ip' => $request->ip()
            ]);

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Carrinho limpo!',
                    'count' => 0
                ]);
            }

            return redirect()->route('carrinho.index')
                ->with('success', 'Carrinho limpo!');
        } catch (\Exception $e) {
            Log::error('❌ Erro ao limpar carrinho', [
                'erro' => $e->getMessage()
            ]);

            return $this->jsonOrBack($request, false, 'Erro ao limpar carrinho. Tente novamente.');
        }
    }

    /**
     * Retorna a contagem de itens no carrinho (API).
     */
    public function count(): JsonResponse
    {
        try {
            $carrinho = $this->getCarrinho();
            $count = $this->contarItens($carrinho);

            return response()->json([
                'count' => $count,
                'success' => true
            ]);
        } catch (\Exception $e) {
            Log::error('❌ Erro ao contar itens do carrinho', [
                'erro' => $e->getMessage()
            ]);

            return response()->json([
                'count' => 0,
                'success' => false,
                'error' => 'Erro ao contar itens'
            ]);
        }
    }

    /**
     * Retorna o total do carrinho (API).
     */
    public function total(): JsonResponse
    {
        try {
            $carrinho = $this->getCarrinhoCompleto();
            $total = $this->calcularTotal($carrinho);

            return response()->json([
                'total' => $total,
                'total_formatado' => $this->formatarMoeda($total),
                'success' => true
            ]);
        } catch (\Exception $e) {
            Log::error('❌ Erro ao calcular total do carrinho', [
                'erro' => $e->getMessage()
            ]);

            return response()->json([
                'total' => 0,
                'total_formatado' => 'R$ 0,00',
                'success' => false,
                'error' => 'Erro ao calcular total'
            ]);
        }
    }

    // ================================================================
    // MÉTODOS PRIVADOS
    // ================================================================

    /**
     * Obtém o carrinho da sessão.
     */
    private function getCarrinho(): array
    {
        $carrinho = Session::get('carrinho', []);

        if (!is_array($carrinho)) {
            Log::warning('⚠️ Carrinho corrompido - resetando', [
                'tipo' => gettype($carrinho),
                'valor' => $carrinho
            ]);
            $this->salvarCarrinho([]);
            return [];
        }

        return $carrinho;
    }

    /**
     * Salva o carrinho na sessão.
     */
    private function salvarCarrinho(array $carrinho): void
    {
        Session::put('carrinho', array_values($carrinho));
    }

    /**
     * Obtém o carrinho completo com dados dos produtos.
     */
    private function getCarrinhoCompleto(): array
    {
        $carrinho = $this->getCarrinho();
        $resultado = [];

        foreach ($carrinho as $item) {
            $produto = Produto::find($item['produto_id']);

            if (!$produto) {
                Log::warning('⚠️ Produto não encontrado no carrinho', [
                    'produto_id' => $item['produto_id']
                ]);
                continue;
            }

            $subtotal = $produto->valor_unitario * $item['quantidade'];

            $resultado[] = [
                'produto_id' => $produto->id,
                'nome' => $produto->descricao,
                'slug' => $produto->slug,
                'quantidade' => $item['quantidade'],
                'preco' => $produto->valor_unitario,
                'preco_formatado' => $this->formatarMoeda($produto->valor_unitario),
                'subtotal' => $subtotal,
                'subtotal_formatado' => $this->formatarMoeda($subtotal),
                'estoque' => $produto->quantidade,
                'imagem' => $produto->imagem_url,
                'disponivel' => $produto->isDisponivel(),
            ];
        }

        return $resultado;
    }

    /**
     * Adiciona um item ao carrinho.
     */
    private function adicionarItemCarrinho(array $carrinho, int $produtoId, int $quantidade, int $estoque): array
    {
        foreach ($carrinho as &$item) {
            if ($item['produto_id'] == $produtoId) {
                $novaQuantidade = $item['quantidade'] + $quantidade;

                if ($novaQuantidade > self::MAX_QUANTITY_PER_ITEM) {
                    throw new \Exception('Quantidade máxima por item é ' . self::MAX_QUANTITY_PER_ITEM);
                }

                if ($novaQuantidade > $estoque) {
                    throw new \Exception("Quantidade total excede o estoque! Disponível: {$estoque}");
                }

                $item['quantidade'] = $novaQuantidade;
                return $carrinho;
            }
        }

        $carrinho[] = [
            'produto_id' => $produtoId,
            'quantidade' => $quantidade
        ];

        return $carrinho;
    }

    /**
     * Calcula o total do carrinho.
     */
    private function calcularTotal(array $carrinho): float
    {
        $total = 0;

        foreach ($carrinho as $item) {
            $total += ($item['preco'] ?? 0) * ($item['quantidade'] ?? 0);
        }

        return (float) $total;
    }

    /**
     * Conta o total de itens no carrinho.
     */
    private function contarItens(array $carrinho): int
    {
        return (int) array_sum(array_column($carrinho, 'quantidade'));
    }

    /**
     * Formata um valor para moeda brasileira.
     */
    private function formatarMoeda(float $valor): string
    {
        return 'R$ ' . number_format($valor, 2, ',', '.');
    }

    /**
     * Retorna JSON ou redirect baseado no tipo de requisição.
     */
    private function jsonOrBack(Request $request, bool $success, string $message, array $extra = []): JsonResponse|RedirectResponse
    {
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(array_merge([
                'success' => $success,
                'message' => $message
            ], $extra));
        }

        return back()->with($success ? 'success' : 'error', $message);
    }
}