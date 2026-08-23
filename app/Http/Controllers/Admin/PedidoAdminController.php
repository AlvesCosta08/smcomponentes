<?php

namespace App\Http\Controllers\Admin;

use App\Domain\Pedidos\Enums\StatusPedidoEnum;
use App\Domain\Pedidos\Repositories\PedidoRepositoryInterface;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdatePedidoStatusRequest;
use App\Models\Pedido;
use App\Models\PedidoItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PedidoAdminController extends Controller
{
    public function __construct(
        protected PedidoRepositoryInterface $repository
    ) {}

    /**
     * Lista todos os pedidos
     */
    public function index(Request $request): View
    {
        try {
            $filters = $request->only(['status', 'data_inicio', 'data_fim', 'search']);
            $filters = array_filter($filters, fn($value) => $value !== null && $value !== '');

            $pedidos = $this->repository->getFiltered($filters, 15);
            $stats = $this->repository->getStats();
            
            $statusList = collect(StatusPedidoEnum::cases())
                ->mapWithKeys(fn($case) => [$case->value => $case->label()])
                ->toArray();

            return view('admin.pedidos.index', compact('pedidos', 'statusList', 'stats'));

        } catch (\Exception $e) {
            Log::error('Erro ao listar pedidos: ' . $e->getMessage());
            
            return view('admin.pedidos.index', [
                'pedidos' => collect(),
                'statusList' => [],
                'stats' => ['total' => 0, 'faturado' => 0, 'pendentes' => 0, 'hoje' => 0],
            ])->with('error', 'Erro ao carregar pedidos. Tente novamente.');
        }
    }

    /**
     * Mostra detalhes de um pedido específico
     */
    public function show(int $id): View|RedirectResponse
    {
        try {
            $pedido = $this->repository->findById($id);
            
            if (!$pedido) {
                return redirect()->route('admin.pedidos.index')->with('error', 'Pedido não encontrado.');
            }

            $statusList = collect(StatusPedidoEnum::cases())
                ->mapWithKeys(fn($case) => [$case->value => $case->label()])
                ->toArray();
            
            $statusColors = collect(StatusPedidoEnum::cases())
                ->mapWithKeys(fn($case) => [$case->value => $case->color()])
                ->toArray();

            return view('admin.pedidos.show', compact('pedido', 'statusList', 'statusColors'));

        } catch (\Exception $e) {
            Log::error('Erro ao mostrar pedido: ' . $e->getMessage());
            return redirect()->route('admin.pedidos.index')->with('error', 'Erro ao carregar detalhes.');
        }
    }

    /**
     * Atualiza o status do pedido
     */
    public function updateStatus(UpdatePedidoStatusRequest $request, int $id): RedirectResponse
    {
        try {
            $pedido = $this->repository->findById($id);
            
            if (!$pedido) {
                return redirect()->back()->with('error', 'Pedido não encontrado.');
            }

            $pedido->status = $request->status;
            $this->repository->update($pedido, ['status' => $request->status]);
            
            $statusLabel = StatusPedidoEnum::from($request->status)->label();
            
            return redirect()->route('admin.pedidos.show', $id)
                ->with('success', "Status do pedido #{$pedido->numero_pedido} alterado para '{$statusLabel}'.");
                
        } catch (\DomainException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        } catch (\Exception $e) {
            Log::error('Erro ao atualizar status: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Erro ao atualizar status.');
        }
    }

    /**
     * Remove um pedido (apenas se cancelado)
     */
    public function destroy(int $id): RedirectResponse
    {
        try {
            $pedido = $this->repository->findById($id);
            
            if (!$pedido) {
                return redirect()->back()->with('error', 'Pedido não encontrado.');
            }

            if ($pedido->status !== StatusPedidoEnum::CANCELADO) {
                return redirect()->back()->with('error', 'Apenas pedidos cancelados podem ser excluídos.');
            }

            $pedido->itens()->delete();
            $pedido->delete();
            
            return redirect()->route('admin.pedidos.index')
                ->with('success', "Pedido #{$pedido->numero_pedido} excluído com sucesso!");
                
        } catch (\Exception $e) {
            Log::error('Erro ao excluir pedido: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Erro ao excluir pedido.');
        }
    }

    /**
     * Relatório de pedidos
     */
    public function relatorio(Request $request): View
    {
        try {
            // Filtros de data
            $dataInicio = $request->get('data_inicio', now()->startOfMonth()->format('Y-m-d'));
            $dataFim = $request->get('data_fim', now()->format('Y-m-d'));
            
            // Query base com filtros de data
            $query = Pedido::query();
            
            if ($dataInicio && $dataFim) {
                $query->whereBetween('created_at', [
                    \Carbon\Carbon::parse($dataInicio)->startOfDay(),
                    \Carbon\Carbon::parse($dataFim)->endOfDay()
                ]);
            }
            
            // Estatísticas
            $totalPedidos = $query->count();
            $totalVendas = $query->where('status', 'entregue')->sum('total') ?? 0;
            $mediaTicket = $totalPedidos > 0 ? $totalVendas / $totalPedidos : 0;
            
            // Vendas por dia (últimos 30 dias)
            $vendasPorDia = Pedido::where('status', 'entregue')
                ->where('created_at', '>=', now()->subDays(30))
                ->selectRaw('DATE(created_at) as data')
                ->selectRaw('COUNT(*) as quantidade')
                ->selectRaw('SUM(total) as total')
                ->groupBy('data')
                ->orderBy('data', 'asc')
                ->get()
                ->map(function($item) {
                    return [
                        'data' => $item->data,
                        'quantidade' => $item->quantidade,
                        'total' => $item->total ?? 0,
                    ];
                })
                ->toArray();
            
            // Pedidos do período (para listagem)
            $pedidos = $query->with(['user'])
                ->orderBy('created_at', 'desc')
                ->paginate(15);
            
            // Vendas por mês (últimos 6 meses)
            $vendasPorMes = Pedido::where('status', 'entregue')
                ->where('created_at', '>=', now()->subMonths(6))
                ->selectRaw('DATE_FORMAT(created_at, "%Y-%m") as mes')
                ->selectRaw('SUM(total) as total')
                ->groupBy('mes')
                ->orderBy('mes', 'asc')
                ->get();
            
            // Top produtos mais vendidos
            $topProdutos = PedidoItem::select('produto_id', 'nome_produto')
                ->selectRaw('SUM(quantidade) as total_quantidade')
                ->selectRaw('SUM(subtotal) as total_vendido')
                ->groupBy('produto_id', 'nome_produto')
                ->orderBy('total_vendido', 'desc')
                ->limit(10)
                ->get();
            
            // Status dos pedidos
            $statusCounts = Pedido::select('status')
                ->selectRaw('count(*) as total')
                ->groupBy('status')
                ->pluck('total', 'status')
                ->toArray();
            
            $pedidosPendentes = $statusCounts['pendente'] ?? 0;
            $pedidosHoje = Pedido::whereDate('created_at', today())->count();
            
            return view('admin.pedidos.relatorio', compact(
                'totalPedidos',
                'totalVendas',
                'mediaTicket',
                'vendasPorDia',
                'vendasPorMes',
                'pedidos',
                'topProdutos',
                'statusCounts',
                'pedidosPendentes',
                'pedidosHoje',
                'dataInicio',
                'dataFim'
            ));
            
        } catch (\Exception $e) {
            Log::error('Erro ao carregar relatório: ' . $e->getMessage());
            return redirect()->route('admin.pedidos.index')
                ->with('error', 'Erro ao carregar relatório.');
        }
    }

    /**
     * Exporta pedidos para CSV
     */
    public function export(Request $request): StreamedResponse|RedirectResponse
    {
        try {
            $filters = $request->only(['status', 'data_inicio', 'data_fim']);
            $filters = array_filter($filters, fn($value) => $value !== null && $value !== '');

            $pedidos = $this->repository->getFiltered($filters, 10000); 
            
            if ($pedidos->isEmpty()) {
                return redirect()->back()->with('warning', 'Nenhum pedido encontrado para exportar.');
            }
            
            $filename = 'pedidos_' . now()->format('Y-m-d_H-i-s') . '.csv';
            
            $headers = [
                'Content-Type' => 'text/csv; charset=UTF-8',
                'Content-Disposition' => "attachment; filename=\"{$filename}\"",
                'Pragma' => 'no-cache',
                'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
                'Expires' => '0',
            ];
            
            $callback = function() use ($pedidos) {
                $handle = fopen('php://output', 'w');
                fputs($handle, "\xEF\xBB\xBF");
                
                fputcsv($handle, [
                    'ID', 'Número', 'Cliente', 'Email', 'Total', 'Status', 'Data'
                ]);
                
                foreach ($pedidos as $pedido) {
                    fputcsv($handle, [
                        $pedido->id,
                        $pedido->numero_pedido,
                        $pedido->user->name ?? 'N/A',
                        $pedido->user->email ?? 'N/A',
                        number_format($pedido->total ?? 0, 2, ',', '.'),
                        StatusPedidoEnum::from($pedido->status)->label(),
                        $pedido->created_at?->format('d/m/Y H:i') ?? 'N/A',
                    ]);
                }
                
                fclose($handle);
            };
            
            return response()->stream($callback, 200, $headers);

        } catch (\Exception $e) {
            Log::error('Erro ao exportar pedidos: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Erro ao exportar dados.');
        }
    }
}