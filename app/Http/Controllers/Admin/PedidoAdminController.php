<?php
// app/Http/Controllers/Admin/PedidoAdminController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdatePedidoStatusRequest;
use App\Models\Pedido;
use App\Services\OrderAdminService;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Carbon\Carbon;

class PedidoAdminController extends Controller
{
    public function __construct(
        protected OrderAdminService $orderAdminService
    ) {}

    /**
     * Lista todos os pedidos
     */
    public function index(Request $request): View
    {
        try {
            $filters = $request->only(['status', 'data_inicio', 'data_fim', 'search']);
            $filters = array_filter($filters, function($value) {
                return $value !== null && $value !== '';
            });

            $pedidos = $this->orderAdminService->listOrders($filters, 15);
            
            // Estatísticas
            $stats = $this->orderAdminService->getStats();
            
            $statusList = Pedido::statusLabels();

            return view('admin.pedidos.index', compact(
                'pedidos',
                'statusList',
                'stats'
            ));

        } catch (\Exception $e) {
            Log::error('Erro ao listar pedidos: ' . $e->getMessage());
            
            return view('admin.pedidos.index', [
                'pedidos' => collect(),
                'statusList' => Pedido::statusLabels(),
                'stats' => [
                    'total' => 0,
                    'faturado' => 0,
                    'pendentes' => 0,
                    'hoje' => 0,
                ],
            ])->with('error', 'Erro ao carregar pedidos: ' . $e->getMessage());
        }
    }

    /**
     * Mostra detalhes de um pedido específico
     */
    public function show(Pedido $pedido): View|RedirectResponse
    {
        try {
            $pedido->load(['user', 'itens.produto']);
            
            $statusList = Pedido::statusLabels();
            $statusColors = Pedido::statusColors();
            
            return view('admin.pedidos.show', compact('pedido', 'statusList', 'statusColors'));

        } catch (\Exception $e) {
            Log::error('Erro ao mostrar pedido: ' . $e->getMessage());
            return redirect()
                ->route('admin.pedidos.index')
                ->with('error', 'Erro ao carregar detalhes do pedido: ' . $e->getMessage());
        }
    }

    /**
     * Atualiza o status do pedido
     */
    public function updateStatus(UpdatePedidoStatusRequest $request, Pedido $pedido): RedirectResponse
    {
        try {
            $this->orderAdminService->updateStatus($pedido, $request->status);
            
            $statusLabels = Pedido::statusLabels();
            $statusLabel = $statusLabels[$request->status] ?? $request->status;
            
            return redirect()
                ->route('admin.pedidos.show', $pedido)
                ->with('success', "Status do pedido #{$pedido->numero_pedido} alterado para '{$statusLabel}'");
                
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Erro ao atualizar status: ' . $e->getMessage());
        }
    }

    /**
     * Remove um pedido (apenas se cancelado)
     */
    public function destroy(Pedido $pedido): RedirectResponse
    {
        try {
            $this->orderAdminService->deleteOrder($pedido);
            
            return redirect()
                ->route('admin.pedidos.index')
                ->with('success', "Pedido #{$pedido->numero_pedido} excluído com sucesso!");
                
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * Exporta pedidos para CSV
     */
    public function export(Request $request): \Symfony\Component\HttpFoundation\StreamedResponse|RedirectResponse
    {
        try {
            $filters = $request->only(['status', 'data_inicio', 'data_fim']);
            $filters = array_filter($filters, function($value) {
                return $value !== null && $value !== '';
            });

            $pedidos = $this->orderAdminService->export($filters);
            
            if ($pedidos->isEmpty()) {
                return redirect()->back()->with('warning', 'Nenhum pedido encontrado para exportar.');
            }
            
            $filename = 'pedidos_' . date('Y-m-d_H-i-s') . '.csv';
            
            $headers = [
                'Content-Type' => 'text/csv; charset=UTF-8',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
                'Pragma' => 'no-cache',
                'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
                'Expires' => '0',
            ];
            
            $callback = function() use ($pedidos) {
                $handle = fopen('php://output', 'w');
                fputs($handle, "\xEF\xBB\xBF");
                
                // Cabeçalhos
                fputcsv($handle, [
                    'ID', 'Número do Pedido', 'Cliente', 'Email', 'Telefone',
                    'Subtotal', 'Desconto', 'Total', 'Status',
                    'Data do Pedido', 'Data Pagamento', 'Data Envio',
                    'Data Entrega', 'Forma Pagamento', 'Status Pagamento'
                ]);
                
                $statusLabels = Pedido::statusLabels();
                
                foreach ($pedidos as $pedido) {
                    fputcsv($handle, [
                        $pedido->id,
                        $pedido->numero_pedido ?? $pedido->id,
                        $pedido->user->name ?? 'N/A',
                        $pedido->user->email ?? 'N/A',
                        $pedido->user->telefone ?? 'N/A',
                        number_format($pedido->subtotal ?? 0, 2, ',', '.'),
                        number_format($pedido->desconto ?? 0, 2, ',', '.'),
                        number_format($pedido->total ?? 0, 2, ',', '.'),
                        $statusLabels[$pedido->status] ?? $pedido->status,
                        $pedido->created_at?->format('d/m/Y H:i') ?? 'N/A',
                        $pedido->data_pagamento?->format('d/m/Y H:i') ?? 'N/A',
                        $pedido->data_envio?->format('d/m/Y H:i') ?? 'N/A',
                        $pedido->data_entrega?->format('d/m/Y H:i') ?? 'N/A',
                        $pedido->forma_pagamento ?? 'N/A',
                        $pedido->status_pagamento ?? 'N/A'
                    ]);
                }
                
                fclose($handle);
            };
            
            return response()->stream($callback, 200, $headers);

        } catch (\Exception $e) {
            Log::error('Erro ao exportar pedidos: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Erro ao exportar: ' . $e->getMessage());
        }
    }

    /**
     * Relatório de vendas
     */
    public function relatorio(Request $request): View
    {
        try {
            $dataInicio = $request->get('data_inicio', now()->startOfMonth()->format('Y-m-d'));
            $dataFim = $request->get('data_fim', now()->format('Y-m-d'));
            
            // Validar datas
            if (!strtotime($dataInicio) || !strtotime($dataFim)) {
                $dataInicio = now()->startOfMonth()->format('Y-m-d');
                $dataFim = now()->format('Y-m-d');
            }
            
            $report = $this->orderAdminService->getSalesReport($dataInicio, $dataFim);
            
            return view('admin.pedidos.relatorio', array_merge($report, [
                'dataInicio' => $dataInicio,
                'dataFim' => $dataFim,
            ]));

        } catch (\Exception $e) {
            Log::error('Erro no relatório de pedidos: ' . $e->getMessage());
            
            return view('admin.pedidos.relatorio', [
                'pedidos' => collect(),
                'totalVendas' => 0,
                'totalPedidos' => 0,
                'mediaTicket' => 0,
                'dataInicio' => now()->startOfMonth()->format('Y-m-d'),
                'dataFim' => now()->format('Y-m-d'),
                'vendasPorDia' => [],
            ])->with('error', 'Erro ao gerar relatório: ' . $e->getMessage());
        }
    }

    /**
     * Dashboard de pedidos (mantido para compatibilidade)
     */
    public function dashboard(): View|RedirectResponse
    {
        try {
            $stats = $this->orderAdminService->getStats();
            $ultimosPedidos = Pedido::with('user')
                ->orderBy('created_at', 'desc')
                ->take(10)
                ->get();

            return view('admin.pedidos.dashboard', array_merge($stats, [
                'ultimosPedidos' => $ultimosPedidos,
            ]));

        } catch (\Exception $e) {
            Log::error('Erro no dashboard de pedidos: ' . $e->getMessage());
            return redirect()
                ->route('admin.pedidos.index')
                ->with('error', 'Erro ao carregar dashboard: ' . $e->getMessage());
        }
    }
}