<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pedido;
use App\Models\PedidoItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class PedidoAdminController extends Controller
{
    /**
     * Lista todos os pedidos
     */
    public function index(Request $request)
    {
        try {
            $query = Pedido::with('user');
            
            // Filtro por status
            if ($request->has('status') && $request->status != '') {
                $query->where('status', $request->status);
            }
            
            // Filtro por data
            if ($request->has('data_inicio') && $request->data_inicio) {
                $query->whereDate('created_at', '>=', $request->data_inicio);
            }
            
            if ($request->has('data_fim') && $request->data_fim) {
                $query->whereDate('created_at', '<=', $request->data_fim);
            }
            
            // Busca por número do pedido ou cliente
            if ($request->has('search') && $request->search) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('numero_pedido', 'LIKE', "%{$search}%")
                      ->orWhereHas('user', function($user) use ($search) {
                          $user->where('name', 'LIKE', "%{$search}%")
                               ->orWhere('email', 'LIKE', "%{$search}%");
                      });
                });
            }
            
            $pedidos = $query->orderBy('created_at', 'desc')->paginate(15);
            
            // ===== ESTATÍSTICAS PARA O DASHBOARD =====
            $totalPedidos = Pedido::count();
            $totalFaturado = Pedido::where('status', 'entregue')->sum('total') ?? 0;
            $pedidosPendentes = Pedido::where('status', 'pendente')->count();
            $pedidosHoje = Pedido::whereDate('created_at', today())->count();
            
            // Status para o filtro
            $statusList = Pedido::statusLabels();
            
            return view('admin.pedidos.index', compact(
                'pedidos',
                'statusList',
                'totalPedidos',
                'totalFaturado',
                'pedidosPendentes',
                'pedidosHoje'
            ));

        } catch (\Exception $e) {
            Log::error('Erro ao listar pedidos: ' . $e->getMessage());
            
            // Dados vazios para fallback
            $pedidos = collect();
            $statusList = Pedido::statusLabels();
            $totalPedidos = 0;
            $totalFaturado = 0;
            $pedidosPendentes = 0;
            $pedidosHoje = 0;

            return view('admin.pedidos.index', compact(
                'pedidos',
                'statusList',
                'totalPedidos',
                'totalFaturado',
                'pedidosPendentes',
                'pedidosHoje'
            ))->with('error', 'Erro ao carregar pedidos: ' . $e->getMessage());
        }
    }

    /**
     * Mostra detalhes de um pedido específico
     */
    public function show(Pedido $pedido)
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
    public function updateStatus(Request $request, Pedido $pedido)
    {
        $request->validate([
            'status' => 'required|in:pendente,pago,processando,enviado,entregue,cancelado'
        ]);
        
        try {
            DB::beginTransaction();
            
            $statusAnterior = $pedido->status;
            $novoStatus = $request->status;
            
            // 🔥 REGRA: Não permitir alterar pedidos já entregues ou cancelados
            if ($statusAnterior === 'entregue') {
                return redirect()->back()->with('error', 'Pedido já entregue não pode ser alterado.');
            }
            
            if ($statusAnterior === 'cancelado') {
                return redirect()->back()->with('error', 'Pedido cancelado não pode ser alterado.');
            }
            
            // 🔥 REGRA: Verificar se pode cancelar
            if ($novoStatus === 'cancelado' && !$pedido->podeCancelar()) {
                return redirect()->back()->with('error', 'Este pedido não pode ser cancelado. Status atual: ' . $pedido->status_label);
            }
            
            // ============================================
            // LÓGICA DE TRANSIÇÃO DE STATUS
            // ============================================
            
            // Se for cancelar, restaurar estoque
            if ($novoStatus == 'cancelado' && $statusAnterior != 'cancelado') {
                foreach ($pedido->itens as $item) {
                    $produto = $item->produto;
                    if ($produto) {
                        $produto->increment('quantidade', $item->quantidade);
                        // Atualizar disponibilidade se estoque > 0
                        if ($produto->quantidade > 0 && $produto->disponibilidade == 'INDISPONIVEL') {
                            $produto->disponibilidade = 'DISPONIVEL';
                            $produto->save();
                        }
                    }
                }
            }
            
            // Se for confirmar pagamento
            if ($novoStatus == 'pago' && $statusAnterior == 'pendente') {
                $pedido->data_pagamento = now();
                $pedido->status_pagamento = 'pago';
            }
            
            // Se for processar
            if ($novoStatus == 'processando' && in_array($statusAnterior, ['pendente', 'pago'])) {
                // Lógica adicional se necessário
            }
            
            // Se for enviar
            if ($novoStatus == 'enviado' && $statusAnterior != 'enviado') {
                $pedido->data_envio = now();
            }
            
            // Se for entregar
            if ($novoStatus == 'entregue' && $statusAnterior != 'entregue') {
                $pedido->data_entrega = now();
            }
            
            // Atualizar status
            $pedido->status = $novoStatus;
            $pedido->save();
            
            DB::commit();
            
            $statusLabels = Pedido::statusLabels();
            $statusLabel = $statusLabels[$novoStatus] ?? $novoStatus;
            
            return redirect()
                ->route('admin.pedidos.show', $pedido)
                ->with('success', "Status do pedido #{$pedido->numero_pedido} alterado de '{$statusAnterior}' para '{$statusLabel}'");
                
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao atualizar status do pedido: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Erro ao atualizar status: ' . $e->getMessage());
        }
    }

    /**
     * Remove um pedido (apenas se cancelado)
     */
    public function destroy(Pedido $pedido)
    {
        try {
            if ($pedido->status != 'cancelado') {
                return redirect()->back()->with('error', 'Apenas pedidos cancelados podem ser excluídos.');
            }
            
            DB::beginTransaction();
            
            // Remover itens
            $pedido->itens()->delete();
            
            // Remover pedido
            $pedido->delete();
            
            DB::commit();
            
            return redirect()
                ->route('admin.pedidos.index')
                ->with('success', "Pedido #{$pedido->numero_pedido} excluído com sucesso!");
                
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao excluir pedido: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Erro ao excluir pedido: ' . $e->getMessage());
        }
    }

    /**
     * Exporta pedidos para CSV
     */
    public function export(Request $request)
    {
        try {
            $query = Pedido::with('user');
            
            if ($request->has('status') && $request->status != '') {
                $query->where('status', $request->status);
            }
            
            if ($request->has('data_inicio') && $request->data_inicio) {
                $query->whereDate('created_at', '>=', $request->data_inicio);
            }
            
            if ($request->has('data_fim') && $request->data_fim) {
                $query->whereDate('created_at', '<=', $request->data_fim);
            }
            
            $pedidos = $query->orderBy('created_at', 'desc')->get();
            
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
                
                // Adicionar BOM para UTF-8
                fputs($handle, "\xEF\xBB\xBF");
                
                // Cabeçalhos
                fputcsv($handle, [
                    'ID',
                    'Número do Pedido',
                    'Cliente',
                    'Email',
                    'Telefone',
                    'Subtotal',
                    'Desconto',
                    'Total',
                    'Status',
                    'Data do Pedido',
                    'Data Pagamento',
                    'Data Envio',
                    'Data Entrega',
                    'Forma Pagamento',
                    'Status Pagamento'
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
                        $pedido->created_at ? $pedido->created_at->format('d/m/Y H:i') : 'N/A',
                        $pedido->data_pagamento ? $pedido->data_pagamento->format('d/m/Y H:i') : 'N/A',
                        $pedido->data_envio ? $pedido->data_envio->format('d/m/Y H:i') : 'N/A',
                        $pedido->data_entrega ? $pedido->data_entrega->format('d/m/Y H:i') : 'N/A',
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
    public function relatorio(Request $request)
    {
        try {
            // Validação das datas
            $dataInicio = $request->get('data_inicio', now()->startOfMonth()->format('Y-m-d'));
            $dataFim = $request->get('data_fim', now()->format('Y-m-d'));
            
            // Validar datas
            if (!strtotime($dataInicio) || !strtotime($dataFim)) {
                $dataInicio = now()->startOfMonth()->format('Y-m-d');
                $dataFim = now()->format('Y-m-d');
            }
            
            // Converter para Carbon
            $inicio = Carbon::parse($dataInicio)->startOfDay();
            $fim = Carbon::parse($dataFim)->endOfDay();
            
            // Buscar pedidos entregues no período
            $pedidos = Pedido::where('status', 'entregue')
                ->whereBetween('created_at', [$inicio, $fim])
                ->with('user')
                ->orderBy('created_at', 'desc')
                ->get();
            
            // Calcular totais
            $totalVendas = $pedidos->sum('total') ?? 0;
            $totalPedidos = $pedidos->count();
            $mediaTicket = $totalPedidos > 0 ? $totalVendas / $totalPedidos : 0;
            
            // Vendas por dia
            $vendasPorDia = $pedidos->groupBy(function($item) {
                return $item->created_at->format('Y-m-d');
            })->map(function($group) {
                return [
                    'data' => $group->first()->created_at->format('d/m/Y'),
                    'total' => $group->sum('total'),
                    'quantidade' => $group->count()
                ];
            })->values()->toArray();
            
            // Ordenar por data
            usort($vendasPorDia, function($a, $b) {
                return strtotime($a['data']) - strtotime($b['data']);
            });
            
            return view('admin.pedidos.relatorio', compact(
                'pedidos',
                'totalVendas',
                'totalPedidos',
                'mediaTicket',
                'dataInicio',
                'dataFim',
                'vendasPorDia'
            ));

        } catch (\Exception $e) {
            Log::error('Erro no relatório de pedidos: ' . $e->getMessage());
            
            // Dados vazios para fallback
            $pedidos = collect();
            $totalVendas = 0;
            $totalPedidos = 0;
            $mediaTicket = 0;
            $dataInicio = now()->startOfMonth()->format('Y-m-d');
            $dataFim = now()->format('Y-m-d');
            $vendasPorDia = [];

            return view('admin.pedidos.relatorio', compact(
                'pedidos',
                'totalVendas',
                'totalPedidos',
                'mediaTicket',
                'dataInicio',
                'dataFim',
                'vendasPorDia'
            ))->with('error', 'Erro ao gerar relatório: ' . $e->getMessage());
        }
    }

    /**
     * Dashboard de pedidos
     */
    public function dashboard()
    {
        try {
            $totalPedidos = Pedido::count();
            $totalEntregues = Pedido::where('status', 'entregue')->count();
            $totalCancelados = Pedido::where('status', 'cancelado')->count();
            $totalPendentes = Pedido::where('status', 'pendente')->count();
            
            $faturamentoTotal = Pedido::where('status', 'entregue')->sum('total') ?? 0;
            $faturamentoMes = Pedido::where('status', 'entregue')
                ->whereMonth('created_at', now()->month)
                ->sum('total') ?? 0;
            
            $pedidosHoje = Pedido::whereDate('created_at', today())->count();
            $faturamentoHoje = Pedido::whereDate('created_at', today())
                ->where('status', 'entregue')
                ->sum('total') ?? 0;
            
            // Últimos pedidos
            $ultimosPedidos = Pedido::with('user')
                ->orderBy('created_at', 'desc')
                ->take(10)
                ->get();

            return view('admin.pedidos.dashboard', compact(
                'totalPedidos',
                'totalEntregues',
                'totalCancelados',
                'totalPendentes',
                'faturamentoTotal',
                'faturamentoMes',
                'pedidosHoje',
                'faturamentoHoje',
                'ultimosPedidos'
            ));

        } catch (\Exception $e) {
            Log::error('Erro no dashboard de pedidos: ' . $e->getMessage());
            return redirect()
                ->route('admin.pedidos.index')
                ->with('error', 'Erro ao carregar dashboard: ' . $e->getMessage());
        }
    }
}