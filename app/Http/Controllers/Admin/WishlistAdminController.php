<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Wishlist;
use App\Models\WishlistItem;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class WishlistAdminController extends Controller
{
    /**
     * Lista todas as wishlists dos usuários.
     */
    public function index(Request $request): View
    {
        $query = Wishlist::with(['user']);

        if ($request->has('busca')) {
            $query->whereHas('user', function($q) use ($request) {
                $q->where('name', 'like', "%{$request->busca}%")
                  ->orWhere('email', 'like', "%{$request->busca}%");
            });
        }

        $wishlists = $query->orderBy('created_at', 'desc')->paginate(20);

        return view('admin.wishlists.index', compact('wishlists'));
    }

    /**
     * Mostra detalhes de uma wishlist.
     */
    public function show(int $id): View
    {
        $wishlist = Wishlist::with(['user', 'items.produto'])
            ->findOrFail($id);

        $totalItems = $wishlist->countItems();

        return view('admin.wishlists.show', compact('wishlist', 'totalItems'));
    }

    /**
     * Remove uma wishlist (admin).
     */
    public function destroy(int $id): RedirectResponse
    {
        $wishlist = Wishlist::findOrFail($id);
        $wishlist->delete();

        return redirect()->route('admin.wishlists.index')
            ->with('success', 'Wishlist removida com sucesso!');
    }

    /**
     * Estatísticas de wishlists.
     */
    public function stats(): View
    {
        $totalWishlists = Wishlist::count();
        $totalItems = WishlistItem::count();
        $usuariosComWishlist = User::has('wishlists')->count();
        $produtosMaisDesejados = WishlistItem::with('produto')
            ->select('produto_id', \DB::raw('count(*) as total'))
            ->groupBy('produto_id')
            ->orderBy('total', 'desc')
            ->limit(10)
            ->get();

        return view('admin.wishlists.stats', compact(
            'totalWishlists',
            'totalItems',
            'usuariosComWishlist',
            'produtosMaisDesejados'
        ));
    }
}