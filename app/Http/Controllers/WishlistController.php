<?php
// app/Http/Controllers/WishlistController.php

namespace App\Http\Controllers;

use App\DTOs\WishlistDTO;
use App\Http\Requests\WishlistRequest;
use App\Models\Produto;
use App\Services\WishlistService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WishlistController extends Controller
{
    public function __construct(
        protected WishlistService $wishlistService
    ) {}

    /**
     * Mostrar página de wishlist do usuário
     */
    public function index(): View
    {
        $wishlists = $this->wishlistService->getUserWishlists(auth()->id());
        $wishlistDefault = $this->wishlistService->getOrCreateDefaultWishlist(auth()->id());
        
        return view('wishlist.index', compact('wishlists', 'wishlistDefault'));
    }

    /**
     * Mostrar wishlist específica
     */
    public function show(int $id): View
    {
        $wishlist = $this->wishlistService->getWishlist($id);
        
        if (!$wishlist || $wishlist->user_id !== auth()->id()) {
            abort(404);
        }

        return view('wishlist.show', compact('wishlist'));
    }

    /**
     * Criar nova wishlist
     */
    public function store(WishlistRequest $request): RedirectResponse
    {
        try {
            $dto = WishlistDTO::fromRequest($request);
            $wishlist = $this->wishlistService->createWishlist($dto);

            return redirect()
                ->route('wishlist.show', $wishlist)
                ->with('success', 'Lista de desejos criada com sucesso!');

        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Erro ao criar lista: ' . $e->getMessage());
        }
    }

    /**
     * Atualizar wishlist
     */
    public function update(WishlistRequest $request, int $id): RedirectResponse
    {
        try {
            $this->wishlistService->updateWishlist($id, $request->validated());

            return redirect()
                ->route('wishlist.show', $id)
                ->with('success', 'Lista atualizada com sucesso!');

        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Erro ao atualizar lista: ' . $e->getMessage());
        }
    }

    /**
     * Deletar wishlist
     */
    public function destroy(int $id): RedirectResponse
    {
        try {
            $this->wishlistService->deleteWishlist($id);

            return redirect()
                ->route('wishlist.index')
                ->with('success', 'Lista removida com sucesso!');

        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Adicionar produto à wishlist (AJAX)
     */
    public function adicionar(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'produto_id' => 'required|exists:produtos,id',
                'wishlist_id' => 'nullable|exists:wishlists,id',
                'observacao' => 'nullable|string|max:255',
            ]);

            $userId = auth()->id();
            $produtoId = $request->produto_id;
            $wishlistId = $request->wishlist_id;

            // Se não especificar wishlist, usar a padrão
            if (!$wishlistId) {
                $wishlist = $this->wishlistService->getOrCreateDefaultWishlist($userId);
                $wishlistId = $wishlist->id;
            }

            $this->wishlistService->addProductToWishlist(
                $wishlistId,
                $produtoId,
                $request->observacao
            );

            return response()->json([
                'success' => true,
                'message' => 'Produto adicionado à lista de desejos!',
                'in_wishlist' => true,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Remover produto da wishlist (AJAX)
     */
    public function remover(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'produto_id' => 'required|exists:produtos,id',
                'wishlist_id' => 'nullable|exists:wishlists,id',
            ]);

            $userId = auth()->id();
            $produtoId = $request->produto_id;
            $wishlistId = $request->wishlist_id;

            if (!$wishlistId) {
                $wishlist = $this->wishlistService->getOrCreateDefaultWishlist($userId);
                $wishlistId = $wishlist->id;
            }

            $this->wishlistService->removeProductFromWishlist($wishlistId, $produtoId);

            return response()->json([
                'success' => true,
                'message' => 'Produto removido da lista de desejos!',
                'in_wishlist' => false,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Verificar se produto está na wishlist (AJAX)
     */
    public function verificar(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'produto_id' => 'required|exists:produtos,id',
            ]);

            $inWishlist = $this->wishlistService->isInWishlist(
                auth()->id(),
                $request->produto_id
            );

            return response()->json([
                'success' => true,
                'in_wishlist' => $inWishlist,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Mover produtos entre listas
     */
    public function mover(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'produto_id' => 'required|exists:produtos,id',
                'origem_id' => 'required|exists:wishlists,id',
                'destino_id' => 'required|exists:wishlists,id|different:origem_id',
            ]);

            // Remover da origem
            $this->wishlistService->removeProductFromWishlist(
                $request->origem_id,
                $request->produto_id
            );

            // Adicionar ao destino
            $this->wishlistService->addProductToWishlist(
                $request->destino_id,
                $request->produto_id
            );

            return response()->json([
                'success' => true,
                'message' => 'Produto movido com sucesso!',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }
}