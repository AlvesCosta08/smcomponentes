<?php

namespace App\Http\Controllers;

use App\Models\Pedido;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        
        // Total de pedidos do usuário
        $totalPedidos = Pedido::where('user_id', $user->id)->count();
        
        // Pedidos pendentes
        $pedidosPendentes = Pedido::where('user_id', $user->id)
            ->whereIn('status', ['pendente', 'pago', 'processando'])
            ->count();
        
        // Total gasto
        $totalGasto = Pedido::where('user_id', $user->id)
            ->whereIn('status', ['entregue', 'pago', 'processando', 'enviado'])
            ->sum('total');
        
        // Últimos pedidos
        $ultimosPedidos = Pedido::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();
        
        return view('dashboard', compact(
            'totalPedidos',
            'pedidosPendentes',
            'totalGasto',
            'ultimosPedidos'
        ));
    }
}