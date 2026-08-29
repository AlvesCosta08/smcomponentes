<?php

namespace App\Http\Controllers;

use App\Models\Produto;
use App\Models\Wishlist;
use App\Models\WishlistItem;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class WishlistController extends Controller
{
    /**
     * Exibe todas as listas de desejos do usuário.
     */
    public function index(Request $request): View|JsonResponse
    {
        $user = Auth::user();
        
        if (!$user) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'error' => 'Unauthorized',
                    'message' => 'Faça login para ver suas listas de desejos.'
                ], 401);
            }
            abort(403, 'Faça login para ver suas listas de desejos.');
        }

        // Buscar todas as wishlists do usuário
        $wishlists = $user->wishlists()
            ->withCount('items')
            ->with(['items.produto'])
            ->orderBy('is_default', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        // ✅ Se for requisição API, retorna JSON
        if ($request->expectsJson() || $request->is('api/*')) {
            $data = $wishlists->map(function($wishlist) {
                return [
                    'id' => $wishlist->id,
                    'nome' => $wishlist->nome,
                    'descricao' => $wishlist->descricao,
                    'is_default' => $wishlist->is_default,
                    'is_public' => $wishlist->is_public,
                    'total_items' => $wishlist->items_count,
                    'items' => $wishlist->items->map(function($item) {
                        return [
                            'id' => $item->id,
                            'produto_id' => $item->produto_id,
                            'observacao' => $item->observacao,
                            'produto' => $item->produto ? [
                                'id' => $item->produto->id,
                                'descricao' => $item->produto->descricao,
                                'slug' => $item->produto->slug,
                                'valor_unitario' => $item->produto->valor_unitario,
                                'preco_promocional' => $item->produto->preco_promocional,
                                'imagem_url' => $item->produto->imagem_url,
                                'disponivel' => $item->produto->isDisponivel(),
                            ] : null
                        ];
                    })
                ];
            });
            
            return response()->json([
                'data' => $data,
                'total' => $wishlists->count()
            ]);
        }

        // ✅ Caso contrário, retorna a view
        return view('wishlist.index', compact('wishlists'));
    }

    /**
     * Exibe uma wishlist específica com seus produtos.
     */
    public function show(Request $request, int $id): View|JsonResponse
    {
        $user = Auth::user();
        
        if (!$user) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'error' => 'Unauthorized',
                    'message' => 'Faça login para ver esta lista.'
                ], 401);
            }
            abort(403, 'Faça login para ver esta lista.');
        }

        $wishlist = Wishlist::where('id', $id)
            ->where('user_id', $user->id)
            ->firstOrFail();

        // Carregar itens com produtos
        $items = $wishlist->items()
            ->with(['produto', 'produto.categoria'])
            ->orderBy('created_at', 'desc')
            ->get();

        $totalItems = $wishlist->countItems();

        // ✅ Se for requisição API, retorna JSON
        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'data' => [
                    'id' => $wishlist->id,
                    'nome' => $wishlist->nome,
                    'descricao' => $wishlist->descricao,
                    'is_default' => $wishlist->is_default,
                    'is_public' => $wishlist->is_public,
                    'total_items' => $totalItems,
                    'items' => $items->map(function($item) {
                        return [
                            'id' => $item->id,
                            'produto_id' => $item->produto_id,
                            'observacao' => $item->observacao,
                            'produto' => $item->produto ? [
                                'id' => $item->produto->id,
                                'descricao' => $item->produto->descricao,
                                'slug' => $item->produto->slug,
                                'valor_unitario' => $item->produto->valor_unitario,
                                'preco_promocional' => $item->produto->preco_promocional,
                                'imagem_url' => $item->produto->imagem_url,
                                'disponivel' => $item->produto->isDisponivel(),
                            ] : null
                        ];
                    })
                ]
            ]);
        }

        // Buscar todas as listas do usuário para o menu lateral
        $wishlists = $user->wishlists()
            ->withCount('items')
            ->orderBy('is_default', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('wishlist.show', compact('wishlist', 'items', 'totalItems', 'wishlists'));
    }

    /**
     * Cria uma nova wishlist.
     */
    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $user = Auth::user();
        
        if (!$user) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'error' => 'Unauthorized',
                    'message' => 'Faça login para criar uma lista de desejos.'
                ], 401);
            }
            return redirect()->route('login')
                ->with('error', 'Faça login para criar uma lista de desejos.');
        }

        $request->validate([
            'nome' => 'required|string|max:100',
            'descricao' => 'nullable|string|max:500',
            'is_public' => 'nullable|boolean',
        ]);

        $wishlist = $user->wishlists()->create([
            'nome' => $request->nome,
            'descricao' => $request->descricao,
            'is_default' => $user->wishlists()->count() === 0,
            'is_public' => $request->boolean('is_public', false),
        ]);

        Log::info('📋 Nova wishlist criada', [
            'user_id' => $user->id,
            'wishlist_id' => $wishlist->id,
            'nome' => $wishlist->nome
        ]);

        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'success' => true,
                'message' => 'Lista de desejos criada com sucesso!',
                'data' => [
                    'id' => $wishlist->id,
                    'nome' => $wishlist->nome,
                    'descricao' => $wishlist->descricao,
                    'is_default' => $wishlist->is_default,
                    'is_public' => $wishlist->is_public,
                ]
            ]);
        }

        // ✅ CORRIGIDO: Usar cliente.wishlist.show
        return redirect()->route('cliente.wishlist.show', $wishlist->id)
            ->with('success', 'Lista de desejos criada com sucesso!');
    }

    /**
     * Atualiza uma wishlist.
     */
    public function update(Request $request, int $id): RedirectResponse|JsonResponse
    {
        $user = Auth::user();
        
        if (!$user) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'error' => 'Unauthorized',
                    'message' => 'Faça login para editar esta lista.'
                ], 401);
            }
            return redirect()->route('login')
                ->with('error', 'Faça login para editar esta lista.');
        }

        $wishlist = Wishlist::where('id', $id)
            ->where('user_id', $user->id)
            ->firstOrFail();

        $request->validate([
            'nome' => 'required|string|max:100',
            'descricao' => 'nullable|string|max:500',
            'is_public' => 'nullable|boolean',
        ]);

        $wishlist->update([
            'nome' => $request->nome,
            'descricao' => $request->descricao,
            'is_public' => $request->boolean('is_public', $wishlist->is_public),
        ]);

        Log::info('📋 Wishlist atualizada', [
            'user_id' => $user->id,
            'wishlist_id' => $wishlist->id,
            'nome' => $wishlist->nome
        ]);

        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'success' => true,
                'message' => 'Lista de desejos atualizada com sucesso!',
                'data' => [
                    'id' => $wishlist->id,
                    'nome' => $wishlist->nome,
                    'descricao' => $wishlist->descricao,
                    'is_default' => $wishlist->is_default,
                    'is_public' => $wishlist->is_public,
                ]
            ]);
        }

        // ✅ CORRIGIDO: Usar cliente.wishlist.show
        return redirect()->route('cliente.wishlist.show', $wishlist->id)
            ->with('success', 'Lista de desejos atualizada com sucesso!');
    }

    /**
     * Exclui uma wishlist.
     */
    public function destroy(Request $request, int $id): RedirectResponse|JsonResponse
    {
        $user = Auth::user();
        
        if (!$user) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'error' => 'Unauthorized',
                    'message' => 'Faça login para excluir esta lista.'
                ], 401);
            }
            return redirect()->route('login')
                ->with('error', 'Faça login para excluir esta lista.');
        }

        $wishlist = Wishlist::where('id', $id)
            ->where('user_id', $user->id)
            ->firstOrFail();

        // Não permitir excluir a lista padrão
        if ($wishlist->is_default) {
            $message = 'Não é possível excluir a lista de desejos padrão.';
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => $message
                ], 400);
            }
            return back()->with('error', $message);
        }

        $wishlist->delete();

        Log::info('🗑️ Wishlist excluída', [
            'user_id' => $user->id,
            'wishlist_id' => $wishlist->id
        ]);

        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'success' => true,
                'message' => 'Lista de desejos excluída com sucesso!'
            ]);
        }

        // ✅ CORRIGIDO: Usar cliente.wishlist.index
        return redirect()->route('cliente.wishlist.index')
            ->with('success', 'Lista de desejos excluída com sucesso!');
    }

    /**
     * Adiciona um produto à wishlist (AJAX).
     */
    public function adicionar(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Faça login para adicionar à lista de desejos.',
                    'redirect' => route('login')
                ], 401);
            }

            $request->validate([
                'produto_id' => 'required|exists:produtos,id',
                'wishlist_id' => 'nullable|exists:wishlists,id',
            ]);

            $produto = Produto::find($request->produto_id);

            if (!$produto || !$produto->isDisponivel()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Produto indisponível!'
                ]);
            }

            // Se wishlist_id for fornecido, usa ela, senão usa a padrão
            if ($request->has('wishlist_id') && $request->wishlist_id) {
                $wishlist = Wishlist::where('id', $request->wishlist_id)
                    ->where('user_id', $user->id)
                    ->firstOrFail();
            } else {
                $wishlist = $user->getOrCreateWishlist();
            }

            // Verificar se já existe
            if ($wishlist->hasProduct($produto->id)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Produto já está na sua lista de desejos!'
                ]);
            }

            // Adicionar produto
            $wishlist->addProduct($produto->id);

            Log::info('❤️ Produto adicionado à wishlist', [
                'user_id' => $user->id,
                'produto_id' => $produto->id,
                'wishlist_id' => $wishlist->id
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Produto adicionado à lista de desejos!',
                'wishlist_count' => $wishlist->countItems(),
                'wishlist_id' => $wishlist->id,
            ]);

        } catch (\Exception $e) {
            Log::error('❌ Erro ao adicionar à wishlist', [
                'erro' => $e->getMessage(),
                'user_id' => Auth::id(),
                'produto_id' => $request->produto_id ?? null
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro ao adicionar produto: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove um produto da wishlist (AJAX).
     */
    public function remover(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Faça login para remover da lista de desejos.'
                ], 401);
            }

            $request->validate([
                'produto_id' => 'required|exists:produtos,id',
                'wishlist_id' => 'nullable|exists:wishlists,id',
            ]);

            // Se wishlist_id for fornecido, usa ela, senão usa a padrão
            if ($request->has('wishlist_id') && $request->wishlist_id) {
                $wishlist = Wishlist::where('id', $request->wishlist_id)
                    ->where('user_id', $user->id)
                    ->firstOrFail();
            } else {
                $wishlist = $user->wishlist()->first();
                
                if (!$wishlist) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Lista de desejos não encontrada.'
                    ]);
                }
            }

            // Remover produto
            $removido = $wishlist->removeProduct($request->produto_id);

            if (!$removido) {
                return response()->json([
                    'success' => false,
                    'message' => 'Produto não encontrado na lista.'
                ]);
            }

            Log::info('💔 Produto removido da wishlist', [
                'user_id' => $user->id,
                'produto_id' => $request->produto_id,
                'wishlist_id' => $wishlist->id
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Produto removido da lista de desejos!',
                'wishlist_count' => $wishlist->countItems(),
                'wishlist_id' => $wishlist->id,
            ]);

        } catch (\Exception $e) {
            Log::error('❌ Erro ao remover da wishlist', [
                'erro' => $e->getMessage(),
                'user_id' => Auth::id(),
                'produto_id' => $request->produto_id ?? null
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro ao remover produto: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Verifica se um produto está na wishlist (AJAX).
     */
    public function verificar(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'in_wishlist' => false,
                    'message' => 'Usuário não autenticado.'
                ]);
            }

            $request->validate([
                'produto_id' => 'required|exists:produtos,id',
            ]);

            $inWishlist = $user->isInWishlist($request->produto_id);

            return response()->json([
                'success' => true,
                'in_wishlist' => $inWishlist,
                'produto_id' => $request->produto_id,
            ]);

        } catch (\Exception $e) {
            Log::error('❌ Erro ao verificar wishlist', [
                'erro' => $e->getMessage(),
                'produto_id' => $request->produto_id ?? null
            ]);

            return response()->json([
                'success' => false,
                'in_wishlist' => false,
                'message' => 'Erro ao verificar: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Move um produto entre wishlists.
     */
    public function mover(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Faça login para mover produtos.'
                ], 401);
            }

            $request->validate([
                'produto_id' => 'required|exists:produtos,id',
                'origem_id' => 'required|exists:wishlists,id',
                'destino_id' => 'required|exists:wishlists,id|different:origem_id',
            ]);

            // Verificar permissão nas duas listas
            $origem = Wishlist::where('id', $request->origem_id)
                ->where('user_id', $user->id)
                ->firstOrFail();

            $destino = Wishlist::where('id', $request->destino_id)
                ->where('user_id', $user->id)
                ->firstOrFail();

            // Remover da origem
            $removido = $origem->removeProduct($request->produto_id);

            if (!$removido) {
                return response()->json([
                    'success' => false,
                    'message' => 'Produto não encontrado na lista de origem.'
                ]);
            }

            // Adicionar ao destino
            $destino->addProduct($request->produto_id);

            Log::info('🔄 Produto movido entre wishlists', [
                'user_id' => $user->id,
                'produto_id' => $request->produto_id,
                'origem_id' => $request->origem_id,
                'destino_id' => $request->destino_id
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Produto movido com sucesso!',
                'origem_count' => $origem->countItems(),
                'destino_count' => $destino->countItems(),
            ]);

        } catch (\Exception $e) {
            Log::error('❌ Erro ao mover produto na wishlist', [
                'erro' => $e->getMessage(),
                'user_id' => Auth::id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro ao mover produto: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Limpa todos os itens de uma wishlist.
     */
    public function limpar(Request $request, int $id): RedirectResponse|JsonResponse
    {
        $user = Auth::user();
        
        if (!$user) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'error' => 'Unauthorized',
                    'message' => 'Faça login para limpar esta lista.'
                ], 401);
            }
            return redirect()->route('login')
                ->with('error', 'Faça login para limpar esta lista.');
        }

        $wishlist = Wishlist::where('id', $id)
            ->where('user_id', $user->id)
            ->firstOrFail();

        $wishlist->clearItems();

        Log::info('🧹 Wishlist limpa', [
            'user_id' => $user->id,
            'wishlist_id' => $wishlist->id
        ]);

        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'success' => true,
                'message' => 'Lista de desejos limpa com sucesso!'
            ]);
        }

        // ✅ CORRIGIDO: Usar cliente.wishlist.show
        return redirect()->route('cliente.wishlist.show', $wishlist->id)
            ->with('success', 'Lista de desejos limpa com sucesso!');
    }

    /**
     * Torna uma wishlist a padrão.
     */
    public function setDefault(Request $request, int $id): RedirectResponse|JsonResponse
    {
        $user = Auth::user();
        
        if (!$user) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'error' => 'Unauthorized',
                    'message' => 'Faça login para definir a lista padrão.'
                ], 401);
            }
            return redirect()->route('login')
                ->with('error', 'Faça login para definir a lista padrão.');
        }

        $wishlist = Wishlist::where('id', $id)
            ->where('user_id', $user->id)
            ->firstOrFail();

        $wishlist->setAsDefault();

        Log::info('⭐ Wishlist definida como padrão', [
            'user_id' => $user->id,
            'wishlist_id' => $wishlist->id,
            'nome' => $wishlist->nome
        ]);

        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'success' => true,
                'message' => 'Lista de desejos definida como padrão!'
            ]);
        }

        // ✅ CORRIGIDO: Usar cliente.wishlist.index
        return redirect()->route('cliente.wishlist.index')
            ->with('success', 'Lista de desejos definida como padrão!');
    }
}