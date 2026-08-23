<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\DashboardService;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\Log;

class DashboardController extends Controller
{
    public function __construct(
        protected DashboardService $dashboardService
    ) {
        $this->middleware(['auth', 'role:Admin']);
    }

    /**
     * Mostrar dashboard administrativo
     */
    public function index(Request $request): View
    {
        try {
            // Obter todas as estatísticas do Service
            $stats = $this->dashboardService->getStats();

            // Extrair dados para a view
            $totalUsuarios = $stats['usuarios']['total'] ?? 0;
            $novosUsuariosHoje = $stats['usuarios']['novos_hoje'] ?? 0;
            
            $totalProdutos = $stats['produtos']['total'] ?? 0;
            $produtosAtivos = $stats['produtos']['ativos'] ?? 0;
            $produtosIndisponiveis = $stats['produtos']['inativos'] ?? 0;
            
            $estoqueBaixo = $stats['produtos']['estoque_baixo'] ?? collect();
            $estoqueZero = $stats['produtos']['estoque_zero'] ?? collect();
            
            $totalPedidos = $stats['pedidos']['total'] ?? 0;
            $pedidosStatus = $stats['pedidos']['status'] ?? [];
            $pedidosPendentesCount = $stats['pedidos']['pendentes'] ?? 0;
            $pedidosHoje = $stats['pedidos']['hoje'] ?? 0;
            $ultimosPedidos = $stats['pedidos']['ultimos'] ?? collect();
            
            $faturamentoTotal = $stats['faturamento']['total'] ?? 0;
            $faturamentoMes = $stats['faturamento']['mes'] ?? 0;
            $faturamentoDia = $stats['faturamento']['dia'] ?? 0;
            $mediaPedidosDia = $stats['faturamento']['media_dia'] ?? 0;
            
            $vendasMensais = $stats['vendas_mensais'] ?? [];
            $clientesTop = $stats['clientes_top'] ?? [];

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
                'clientesTop'
            ));

        } catch (\Exception $e) {
            Log::error('Erro no dashboard: ' . $e->getMessage());
            
            // Dados vazios para fallback
            return view('admin.dashboard', [
                'totalUsuarios' => 0,
                'novosUsuariosHoje' => 0,
                'totalProdutos' => 0,
                'produtosAtivos' => 0,
                'produtosIndisponiveis' => 0,
                'estoqueBaixo' => collect(),
                'estoqueZero' => collect(),
                'totalPedidos' => 0,
                'pedidosStatus' => [],
                'pedidosPendentesCount' => 0,
                'pedidosHoje' => 0,
                'ultimosPedidos' => collect(),
                'faturamentoTotal' => 0,
                'faturamentoMes' => 0,
                'faturamentoDia' => 0,
                'mediaPedidosDia' => 0,
                'vendasMensais' => [],
                'clientesTop' => [],
            ])->with('error', 'Erro ao carregar dashboard: ' . $e->getMessage());
        }
    }
}