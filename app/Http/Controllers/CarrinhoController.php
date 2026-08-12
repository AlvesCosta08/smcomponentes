<?php

namespace App\Http\Controllers;

use App\Models\Produto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class CarrinhoController extends Controller
{
    // 🔥 NOVO: Limite máximo de itens no carrinho
    const MAX_ITEMS = 50;
    const MAX_QUANTITY_PER_ITEM = 999;

    public function index()
    {
        $carrinho = Session::get('carrinho', []);
        $total = 0;

        foreach ($carrinho as &$item) {
            $produto = Produto::find($item['produto_id']);
            if ($produto) {
                $item['nome'] = $produto->descricao;
                $item['preco'] = $produto->preco_atual;
                $item['subtotal'] = $item['preco'] * $item['quantidade'];
                $total += $item['subtotal'];
                $item['estoque'] = $produto->quantidade; // 🔥 NOVO: Mostra estoque disponível
            } else {
                // 🔥 NOVO: Remove produtos que não existem mais
                unset($item);
            }
        }

        // Reindexa o array
        $carrinho = array_values($carrinho);
        Session::put('carrinho', $carrinho);

        return view('carrinho.index', compact('carrinho', 'total'));
    }

    public function adicionar(Request $request)
    {
        $request->validate([
            'produto_id' => 'required|exists:produtos,id',
            'quantidade' => 'required|integer|min:1|max:' . self::MAX_QUANTITY_PER_ITEM
        ]);

        $produto = Produto::find($request->produto_id);

        if (!$produto->esta_disponivel) {
            return back()->with('error', 'Produto indisponível!');
        }

        if ($request->quantidade > $produto->quantidade) {
            return back()->with('error', 'Quantidade indisponível em estoque!');
        }

        $carrinho = Session::get('carrinho', []);

        // 🔥 NOVO: Verifica limite máximo de itens
        if (count($carrinho) >= self::MAX_ITEMS) {
            return back()->with('error', 'Carrinho cheio! Limite de ' . self::MAX_ITEMS . ' itens.');
        }

        // Verifica se o produto já está no carrinho
        $found = false;
        foreach ($carrinho as &$item) {
            if ($item['produto_id'] == $request->produto_id) {
                $novaQuantidade = $item['quantidade'] + $request->quantidade;

                // 🔥 NOVO: Verifica limite por item
                if ($novaQuantidade > self::MAX_QUANTITY_PER_ITEM) {
                    return back()->with('error', 'Quantidade máxima por item é ' . self::MAX_QUANTITY_PER_ITEM);
                }

                if ($novaQuantidade > $produto->quantidade) {
                    return back()->with('error', 'Quantidade total excede o estoque!');
                }
                $item['quantidade'] = $novaQuantidade;
                $found = true;
                break;
            }
        }

        if (!$found) {
            $carrinho[] = [
                'produto_id' => $request->produto_id,
                'quantidade' => $request->quantidade
            ];
        }

        Session::put('carrinho', $carrinho);

        // 🔥 NOVO: Retorna JSON para requisições AJAX
        if ($request->ajax()) {
            $totalItems = array_sum(array_column($carrinho, 'quantidade'));
            return response()->json([
                'success' => true,
                'message' => 'Produto adicionado ao carrinho!',
                'count' => $totalItems
            ]);
        }

        return back()->with('success', 'Produto adicionado ao carrinho!');
    }

    public function remover($index)
    {
        $carrinho = Session::get('carrinho', []);

        if (isset($carrinho[$index])) {
            unset($carrinho[$index]);
            $carrinho = array_values($carrinho);
            Session::put('carrinho', $carrinho);
            return back()->with('success', 'Item removido do carrinho!');
        }

        return back()->with('error', 'Item não encontrado!');
    }

    public function atualizar(Request $request, $index)
    {
        $request->validate([
            'quantidade' => 'required|integer|min:1|max:' . self::MAX_QUANTITY_PER_ITEM
        ]);

        $carrinho = Session::get('carrinho', []);

        if (isset($carrinho[$index])) {
            $produto = Produto::find($carrinho[$index]['produto_id']);

            if (!$produto) {
                unset($carrinho[$index]);
                $carrinho = array_values($carrinho);
                Session::put('carrinho', $carrinho);
                return back()->with('error', 'Produto não encontrado!');
            }

            if ($request->quantidade > $produto->quantidade) {
                return back()->with('error', 'Quantidade indisponível! Estoque: ' . $produto->quantidade);
            }

            $carrinho[$index]['quantidade'] = $request->quantidade;
            Session::put('carrinho', $carrinho);

            // 🔥 NOVO: Retorna JSON para requisições AJAX
            if ($request->ajax()) {
                $totalItems = array_sum(array_column($carrinho, 'quantidade'));
                return response()->json([
                    'success' => true,
                    'message' => 'Carrinho atualizado!',
                    'count' => $totalItems,
                    'item_total' => $carrinho[$index]['quantidade'] * $produto->preco_atual
                ]);
            }

            return back()->with('success', 'Carrinho atualizado!');
        }

        return back()->with('error', 'Item não encontrado!');
    }

    // 🔥 NOVO: Limpar carrinho
    public function limpar(Request $request)
    {
        Session::forget('carrinho');

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Carrinho limpo!',
                'count' => 0
            ]);
        }

        return redirect()->route('carrinho.index')->with('success', 'Carrinho limpo!');
    }

    // 🔥 NOVO: Verificar se carrinho está vazio
    public function isEmpty()
    {
        $carrinho = Session::get('carrinho', []);
        return empty($carrinho);
    }

    public function count()
    {
        $carrinho = Session::get('carrinho', []);
        $count = array_sum(array_column($carrinho, 'quantidade'));

        return response()->json(['count' => $count]);
    }

    // 🔥 NOVO: Obter total do carrinho
    public function total()
    {
        $carrinho = Session::get('carrinho', []);
        $total = 0;

        foreach ($carrinho as $item) {
            $produto = Produto::find($item['produto_id']);
            if ($produto) {
                $total += $produto->preco_atual * $item['quantidade'];
            }
        }

        return response()->json(['total' => $total]);
    }
}