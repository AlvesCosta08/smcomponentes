<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pedido;
use App\Models\Produto;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Mostrar dashboard administrativo
     */
    public function index(Request $request)
    {
        // ===== ESTATÍSTICAS BÁSICAS =====
        
        // Usuários
        $totalUsuarios = User::count();
        $novosUsuariosHoje = User::whereDate('created_at', today())->count();
        
        // Produtos
        $totalProdutos = Produto::count();
        $produtosAtivos = Produto::where('ativo', true)->count();
        $produtosIndisponiveis = Produto::where('ativo', false)->count();
        
        // Estoque crítico
        $estoqueBaixo = Produto::where('quantidade', '>', 0)
            ->where('quantidade', '<=', 5)
            ->where('ativo', true)
            ->get();
        
        $estoqueZero = Produto::where('quantidade', 0)
            ->where('ativo', true)
            ->get();
        
        // ===== PEDIDOS =====
        
        // Total de pedidos
        $totalPedidos = Pedido::count();
        
        // Pedidos por status
        $pedidosStatus = Pedido::select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();
        
        // Pedidos pendentes (para o badge)
        $pedidosPendentesCount = Pedido::where('status', 'pendente')->count();
        
        // Pedidos hoje
        $pedidosHoje = Pedido::whereDate('created_at', today())->count();
        
        // Últimos pedidos (últimos 10)
        $ultimosPedidos = Pedido::with('user')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();
        
        // ===== FATURAMENTO =====
        
        // Faturamento total
        $faturamentoTotal = Pedido::where('status', 'entregue')->sum('total');
        
        // Faturamento do mês
        $faturamentoMes = Pedido::where('status', 'entregue')
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('total');
        
        // Faturamento do dia
        $faturamentoDia = Pedido::where('status', 'entregue')
            ->whereDate('created_at', today())
            ->sum('total');
        
        // Média de pedidos por dia (últimos 30 dias)
        $mediaPedidosDia = Pedido::where('created_at', '>=', now()->subDays(30))
            ->where('status', 'entregue')
            ->count() / 30;
        
        // ===== VENDAS MENSAIS (últimos 6 meses) =====
        $vendasMensais = Pedido::where('status', 'entregue')
            ->select(
                DB::raw('DATE_FORMAT(created_at, "%Y-%m") as mes'),
                DB::raw('SUM(total) as total')
            )
            ->where('created_at', '>=', now()->subMonths(6))
            ->groupBy('mes')
            ->orderBy('mes', 'asc')
            ->get()
            ->map(function($item) {
                $meses = [
                    '01' => 'Jan', '02' => 'Fev', '03' => 'Mar',
                    '04' => 'Abr', '05' => 'Mai', '06' => 'Jun',
                    '07' => 'Jul', '08' => 'Ago', '09' => 'Set',
                    '10' => 'Out', '11' => 'Nov', '12' => 'Dez'
                ];
                $parts = explode('-', $item->mes);
                return [
                    'mes' => $meses[$parts[1]] ?? $parts[1],
                    'total' => $item->total
                ];
            })
            ->toArray();
        
        // ===== TOP CLIENTES =====
        $clientesTop = User::select('users.*')
            ->selectRaw('COUNT(pedidos.id) as total_pedidos')
            ->selectRaw('SUM(pedidos.total) as total_gasto')
            ->join('pedidos', 'users.id', '=', 'pedidos.user_id')
            ->where('pedidos.status', 'entregue')
            ->groupBy('users.id')
            ->orderBy('total_gasto', 'desc')
            ->limit(5)
            ->get();
        
        // ===== VARIÁVEIS PARA O LAYOUT =====
        $estoqueBaixoCount = $estoqueBaixo->count() + $estoqueZero->count();
        
        return view('admin.dashboard', compact(
            'totalUsuarios',
            'novosUsuariosHoje',
            'totalProdutos',
            'produtosAtivos',
            'produtosIndisponiveis',
            'estoqueBaixo',
            'estoqueZero',
            'totalPedidos',
            'pedidosStatus',
            'pedidosPendentesCount',
            'pedidosHoje',
            'ultimosPedidos',
            'faturamentoTotal',
            'faturamentoMes',
            'faturamentoDia',
            'mediaPedidosDia',
            'vendasMensais',
            'clientesTop',
            'estoqueBaixoCount'
        ));
    }
}