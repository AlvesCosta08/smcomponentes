<?php
// app/Http/Controllers/Admin/DashboardController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\DashboardService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(
        protected DashboardService $dashboardService
    ) {}

    /**
     * Mostrar dashboard administrativo
     */
    public function index(Request $request): View
    {
        try {
            // Obter todas as estatísticas do Service
            $stats = $this->dashboardService->getStats();

            // Extrair dados para a view
            $totalUsuarios = $stats['usuarios']['total'];
            $novosUsuariosHoje = $stats['usuarios']['novos_hoje'];
            
            $totalProdutos = $stats['produtos']['total'];
            $produtosAtivos = $stats['produtos']['ativos'];
            $produtosIndisponiveis = $stats['produtos']['inativos'];
            
            $estoqueBaixo = $stats['produtos']['estoque_baixo'];
            $estoqueZero = $stats['produtos']['estoque_zero'];
            $estoqueBaixoCount = $estoqueBaixo->count() + $estoqueZero->count();
            
            $totalPedidos = $stats['pedidos']['total'];
            $pedidosStatus = [
                'pendente' => $stats['pedidos']['pendentes'],
                'processando' => $stats['pedidos']['processando'],
                'enviado' => $stats['pedidos']['enviados'],
                'entregue' => $stats['pedidos']['entregues'],
                'cancelado' => $stats['pedidos']['cancelados'],
            ];
            $pedidosPendentesCount = $stats['pedidos']['pendentes'];
            $pedidosHoje = $stats['pedidos']['hoje'];
            $ultimosPedidos = $stats['pedidos']['ultimos'];
            
            $faturamentoTotal = $stats['faturamento']['total'];
            $faturamentoMes = $stats['faturamento']['mes'];
            $faturamentoDia = $stats['faturamento']['dia'];
            $mediaPedidosDia = $stats['faturamento']['media_dia'];
            
            $vendasMensais = $stats['vendas_mensais'];
            $clientesTop = $stats['clientes_top'];

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
                'estoqueBaixoCount' => 0,
            ])->with('error', 'Erro ao carregar dashboard: ' . $e->getMessage());
        }
    }
}