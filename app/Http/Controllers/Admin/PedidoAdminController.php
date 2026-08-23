<?php

namespace App\Http\Controllers\Admin;

use App\Domain\Pedidos\Enums\StatusPedidoEnum;
use App\Domain\Pedidos\Repositories\PedidoRepositoryInterface;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdatePedidoStatusRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

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
            $stats = $this->repository->getStats(); // Certifique-se que este método existe no seu Repository
            
            // Mapeamento do Enum para a View (Fonte única da verdade)
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
     * Atualiza o status do pedido (Delegado para um Handler de Domínio)
     */
    public function updateStatus(UpdatePedidoStatusRequest $request, int $id): RedirectResponse
    {
        try {
            $pedido = $this->repository->findById($id);
            
            if (!$pedido) {
                return redirect()->back()->with('error', 'Pedido não encontrado.');
            }

            // Aqui você deve chamar um Handler, ex: (new UpdateOrderStatusHandler())->handle($pedido, $request->status);
            // Por enquanto, atualizamos via repositório, mas a validação de domínio deve ocorrer no Model/Entity
            $pedido->status = $request->status;
            $this->repository->update($pedido, ['status' => $request->status]);
            
            $statusLabel = StatusPedidoEnum::from($request->status)->label();
            
            return redirect()->route('admin.pedidos.show', $id)
                ->with('success', "Status do pedido #{$pedido->numero_pedido} alterado para '{$statusLabel}'.");
                
        } catch (\DomainException $e) {
            // Captura exceções de regra de negócio (ex: tentar cancelar um pedido já enviado)
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

            // Regra de Domínio: Só pode deletar se estiver cancelado
            if ($pedido->status !== StatusPedidoEnum::CANCELADO) {
                return redirect()->back()->with('error', 'Apenas pedidos cancelados podem ser exclcidos.');
            }

            $this->repository->delete($pedido);
            
            return redirect()->route('admin.pedidos.index')
                ->with('success', "Pedido #{$pedido->numero_pedido} excluído com sucesso!");
                
        } catch (\Exception $e) {
            Log::error('Erro ao excluir pedido: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Erro ao excluir pedido.');
        }
    }

    /**
     * Exporta pedidos para CSV
     */
    public function export(Request $request): \Symfony\Component\HttpFoundation\StreamedResponse|RedirectResponse
    {
        try {
            $filters = $request->only(['status', 'data_inicio', 'data_fim']);
            $filters = array_filter($filters, fn($value) => $value !== null && $value !== '');

            // O repositório deve ter um método getForExport ou similar, ou usamos getFiltered com perPage alto
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
                fputs($handle, "\xEF\xBB\xBF"); // BOM para UTF-8 no Excel
                
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