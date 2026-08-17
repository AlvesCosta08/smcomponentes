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
use Illuminate\Database\Eloquent\ModelNotFoundException;

class CarrinhoController extends Controller
{
    /**
     * Limites do carrinho
     * Tornando públicos para acesso externo (ex: validação)
     */
    public const MAX_ITEMS = 50;
    public const MAX_QUANTITY_PER_ITEM = 999;
    public const MIN_QUANTITY_PER_ITEM = 1;

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
                'maxQuantity' => self::MAX_QUANTITY_PER_ITEM,
                'minQuantity' => self::MIN_QUANTITY_PER_ITEM,
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
                'totalItems' => 0,
                'maxQuantity' => self::MAX_QUANTITY_PER_ITEM,
                'minQuantity' => self::MIN_QUANTITY_PER_ITEM,
                'error' => 'Erro ao carregar o carrinho. Tente novamente.'
            ]);
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
                return $this->jsonOrBack($request, false, 'Produto indisponível no momento!');
            }

            // Verificar estoque
            if ($request->quantidade > $produto->quantidade) {
                return $this->jsonOrBack(
                    $request,
                    false,
                    "Quantidade indisponível em estoque! Disponível: {$produto->quantidade}"
                );
            }

            $carrinho = $this->getCarrinho();

            // Verificar limite de itens diferentes
            if (count($carrinho) >= self::MAX_ITEMS) {
                return $this->jsonOrBack(
                    $request,
                    false,
                    "Carrinho cheio! Limite de " . self::MAX_ITEMS . " itens diferentes."
                );
            }

            // Verificar limite por item
            $quantidadeAtual = $this->getQuantidadeExistente($carrinho, $produto->id);
            $novaQuantidade = $quantidadeAtual + $request->quantidade;

            if ($novaQuantidade > self::MAX_QUANTITY_PER_ITEM) {
                return $this->jsonOrBack(
                    $request,
                    false,
                    "Quantidade máxima por item é " . self::MAX_QUANTITY_PER_ITEM . " unidades."
                );
            }

            if ($novaQuantidade > $produto->quantidade) {
                return $this->jsonOrBack(
                    $request,
                    false,
                    "Quantidade total excede o estoque! Disponível: {$produto->quantidade}"
                );
            }

            // Adicionar ou atualizar item
            $carrinho = $this->adicionarItemCarrinho($carrinho, $produto->id, $request->quantidade);
            $this->salvarCarrinho($carrinho);

            $totalItems = $this->contarItens($carrinho);
            $total = $this->calcularTotal($this->getCarrinhoCompleto());

            Log::info('🛒 Produto adicionado ao carrinho', [
                'produto_id' => $request->produto_id,
                'quantidade' => $request->quantidade,
                'total_items' => $totalItems,
                'usuario_id' => auth()->id() ?? 'guest'
            ]);

            return $this->jsonOrBack($request, true, 'Produto adicionado ao carrinho!', [
                'count' => $totalItems,
                'total' => $total,
                'total_formatado' => $this->formatarMoeda($total)
            ]);
        } catch (\Exception $e) {
            Log::error('❌ Erro ao adicionar produto ao carrinho', [
                'erro' => $e->getMessage(),
                'produto_id' => $request->produto_id ?? null,
                'trace' => $e->getTraceAsString()
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
                'produto_id' => $produtoId,
                'usuario_id' => auth()->id() ?? 'guest'
            ]);

            return redirect()->route('carrinho.index')
                ->with('success', 'Item removido do carrinho!');
        } catch (\Exception $e) {
            Log::error('❌ Erro ao remover item do carrinho', [
                'erro' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return back()->with('error', 'Erro ao remover item. Tente novamente.');
        }
    }

    /**
     * Remove um item do carrinho via AJAX.
     */
    public function removerAjax(Request $request): JsonResponse
    {
        try {
            $produtoId = $request->input('produto_id');
            
            if (!$produtoId) {
                return response()->json([
                    'success' => false,
                    'message' => 'ID do produto não informado.'
                ], 422);
            }

            $carrinho = $this->getCarrinho();
            $removido = false;

            foreach ($carrinho as $key => $item) {
                if ($item['produto_id'] == $produtoId) {
                    unset($carrinho[$key]);
                    $removido = true;
                    break;
                }
            }

            if (!$removido) {
                return response()->json([
                    'success' => false,
                    'message' => 'Produto não encontrado no carrinho.'
                ], 404);
            }

            $this->salvarCarrinho(array_values($carrinho));
            $totalItems = $this->contarItens($carrinho);
            $total = $this->calcularTotal($this->getCarrinhoCompleto());

            Log::info('🗑️ Item removido do carrinho (AJAX)', [
                'produto_id' => $produtoId,
                'usuario_id' => auth()->id() ?? 'guest'
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Item removido do carrinho!',
                'count' => $totalItems,
                'total' => $total,
                'total_formatado' => $this->formatarMoeda($total)
            ]);
        } catch (\Exception $e) {
            Log::error('❌ Erro ao remover item do carrinho (AJAX)', [
                'erro' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro ao remover item. Tente novamente.'
            ], 500);
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

            // Validar quantidade
            if ($request->quantidade < self::MIN_QUANTITY_PER_ITEM) {
                return $this->jsonOrBack(
                    $request,
                    false,
                    "Quantidade mínima é " . self::MIN_QUANTITY_PER_ITEM . " unidade."
                );
            }

            if ($request->quantidade > self::MAX_QUANTITY_PER_ITEM) {
                return $this->jsonOrBack(
                    $request,
                    false,
                    "Quantidade máxima é " . self::MAX_QUANTITY_PER_ITEM . " unidades."
                );
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

            $totalItems = $this->contarItens($carrinho);
            $itemSubtotal = $request->quantidade * $produto->getPrecoVenda();
            $total = $this->calcularTotal($this->getCarrinhoCompleto());

            Log::info('🔄 Carrinho atualizado', [
                'index' => $index,
                'nova_quantidade' => $request->quantidade,
                'produto_id' => $produto->id,
                'usuario_id' => auth()->id() ?? 'guest'
            ]);

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Carrinho atualizado!',
                    'count' => $totalItems,
                    'total' => $total,
                    'total_formatado' => $this->formatarMoeda($total),
                    'item_total' => $itemSubtotal,
                    'item_total_formatado' => $this->formatarMoeda($itemSubtotal)
                ]);
            }

            return redirect()->route('carrinho.index')
                ->with('success', 'Carrinho atualizado!');
        } catch (\Exception $e) {
            Log::error('❌ Erro ao atualizar carrinho', [
                'erro' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
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
                'user_id' => auth()->id() ?? 'guest',
                'ip' => $request->ip()
            ]);

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Carrinho limpo!',
                    'count' => 0,
                    'total' => 0,
                    'total_formatado' => 'R$ 0,00'
                ]);
            }

            return redirect()->route('carrinho.index')
                ->with('success', 'Carrinho limpo!');
        } catch (\Exception $e) {
            Log::error('❌ Erro ao limpar carrinho', [
                'erro' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
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
                'erro' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'count' => 0,
                'success' => false,
                'error' => 'Erro ao contar itens'
            ], 500);
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
                'erro' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'total' => 0,
                'total_formatado' => 'R$ 0,00',
                'success' => false,
                'error' => 'Erro ao calcular total'
            ], 500);
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
        try {
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
        } catch (\Exception $e) {
            Log::error('❌ Erro ao obter carrinho', [
                'erro' => $e->getMessage()
            ]);
            return [];
        }
    }

    /**
     * Salva o carrinho na sessão.
     */
    private function salvarCarrinho(array $carrinho): void
    {
        try {
            Session::put('carrinho', array_values($carrinho));
            Session::save();
        } catch (\Exception $e) {
            Log::error('❌ Erro ao salvar carrinho', [
                'erro' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Obtém o carrinho completo com dados dos produtos.
     */
    private function getCarrinhoCompleto(): array
    {
        try {
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

                $preco = $produto->getPrecoVenda();
                $subtotal = $preco * $item['quantidade'];

                $resultado[] = [
                    'produto_id' => $produto->id,
                    'nome' => $produto->descricao,
                    'slug' => $produto->slug,
                    'quantidade' => $item['quantidade'],
                    'preco' => $preco,
                    'preco_formatado' => $this->formatarMoeda($preco),
                    'subtotal' => $subtotal,
                    'subtotal_formatado' => $this->formatarMoeda($subtotal),
                    'estoque' => $produto->quantidade,
                    'imagem' => $produto->imagem_url,
                    'disponivel' => $produto->isDisponivel(),
                    'tem_promocao' => $produto->tem_promocao,
                    'preco_original' => $produto->valor_unitario,
                    'preco_original_formatado' => $this->formatarMoeda($produto->valor_unitario),
                ];
            }

            return $resultado;
        } catch (\Exception $e) {
            Log::error('❌ Erro ao obter carrinho completo', [
                'erro' => $e->getMessage()
            ]);
            return [];
        }
    }

    /**
     * Adiciona um item ao carrinho.
     */
    private function adicionarItemCarrinho(array $carrinho, int $produtoId, int $quantidade): array
    {
        foreach ($carrinho as &$item) {
            if ($item['produto_id'] == $produtoId) {
                $novaQuantidade = $item['quantidade'] + $quantidade;

                if ($novaQuantidade > self::MAX_QUANTITY_PER_ITEM) {
                    throw new \Exception('Quantidade máxima por item é ' . self::MAX_QUANTITY_PER_ITEM);
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
     * Obtém a quantidade existente de um produto no carrinho.
     */
    private function getQuantidadeExistente(array $carrinho, int $produtoId): int
    {
        foreach ($carrinho as $item) {
            if ($item['produto_id'] == $produtoId) {
                return $item['quantidade'];
            }
        }
        return 0;
    }

    /**
     * Calcula o total do carrinho.
     */
    private function calcularTotal(array $carrinho): float
    {
        try {
            $total = 0;

            foreach ($carrinho as $item) {
                $total += ($item['preco'] ?? 0) * ($item['quantidade'] ?? 0);
            }

            return (float) $total;
        } catch (\Exception $e) {
            Log::error('❌ Erro ao calcular total', [
                'erro' => $e->getMessage()
            ]);
            return 0;
        }
    }

    /**
     * Conta o total de itens no carrinho.
     */
    private function contarItens(array $carrinho): int
    {
        try {
            return (int) array_sum(array_column($carrinho, 'quantidade'));
        } catch (\Exception $e) {
            Log::error('❌ Erro ao contar itens', [
                'erro' => $e->getMessage()
            ]);
            return 0;
        }
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