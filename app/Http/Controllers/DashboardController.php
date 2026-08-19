<?php

namespace App\Http\Controllers;

use App\Models\Pedido;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Cache;

class DashboardController extends Controller
{
    /**
     * Tempo de cache em segundos (5 minutos)
     */
    private const CACHE_TTL = 300;

    /**
     * Exibe o dashboard do usuário.
     */
    public function index(Request $request): View|RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();

        // Verificar se é Admin
        if ($this->isAdmin($user)) {
            return redirect()->route('admin.dashboard');
        }

        // Obter estatísticas com cache
        $stats = $this->getUserStats($user);

        // Últimos pedidos
        $ultimosPedidos = $this->getUltimosPedidos($user);

        return view('cliente.dashboard', array_merge(
            $stats,
            [
                'user' => $user,
                'ultimosPedidos' => $ultimosPedidos,
            ]
        ));
    }

    // ================================================================
    // MÉTODOS PRIVADOS
    // ================================================================

    /**
     * Verifica se o usuário é administrador.
     */
    private function isAdmin(User $user): bool
    {
        try {
            return $user->hasRole('Admin');
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Obtém estatísticas do usuário com cache.
     */
    private function getUserStats(User $user): array
    {
        $cacheKey = "user_stats_{$user->id}";

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($user) {
            $pedidosQuery = Pedido::where('user_id', $user->id);

            $totalPedidos = $pedidosQuery->count();

            $pedidosPendentes = Pedido::where('user_id', $user->id)
                ->whereIn('status', ['pendente', 'pago', 'processando'])
                ->count();

            $totalGasto = Pedido::where('user_id', $user->id)
                ->whereIn('status', ['entregue', 'pago', 'processando', 'enviado'])
                ->sum('total') ?? 0;

            $wishlistCount = $this->getWishlistCount($user);

            return [
                'totalPedidos' => $totalPedidos,
                'pedidosPendentes' => $pedidosPendentes,
                'totalGasto' => $totalGasto,
                'totalGastoFormatado' => 'R$ ' . number_format($totalGasto, 2, ',', '.'),
                'wishlistCount' => $wishlistCount,
                'temPedidos' => $totalPedidos > 0,
                'temPendentes' => $pedidosPendentes > 0,
            ];
        });
    }

    /**
     * Obtém o total de itens na wishlist do usuário.
     */
    private function getWishlistCount(User $user): int
    {
        try {
            $wishlist = $user->wishlist;
            
            if (!$wishlist) {
                return 0;
            }

            return $wishlist->items()->count();
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * Obtém os últimos pedidos do usuário.
     */
    private function getUltimosPedidos(User $user): \Illuminate\Database\Eloquent\Collection
    {
        $cacheKey = "user_pedidos_recentes_{$user->id}";

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($user) {
            return Pedido::where('user_id', $user->id)
                ->with(['itens'])
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get();
        });
    }

    /**
     * Limpa o cache do usuário.
     */
    public function clearCache(Request $request): RedirectResponse
    {
        $user = Auth::user();
        
        if ($user) {
            Cache::forget("user_stats_{$user->id}");
            Cache::forget("user_pedidos_recentes_{$user->id}");
        }

        return back()->with('success', 'Cache do dashboard limpo com sucesso!');
    }

    /**
     * Obtém estatísticas para API (JSON).
     */
    public function stats(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            /** @var User $user */
            $user = Auth::user();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Usuário não autenticado'
                ], 401);
            }

            $stats = $this->getUserStats($user);

            return response()->json([
                'success' => true,
                'data' => $stats
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao buscar estatísticas: ' . $e->getMessage()
            ], 500);
        }
    }
}