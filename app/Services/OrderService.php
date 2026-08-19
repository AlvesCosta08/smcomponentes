<?php
// app/Services/OrderService.php

namespace App\Services;

use App\DTOs\OrderDTO;
use App\DTOs\Responses\OrderResponseDTO;
use App\Events\OrderCreated;
use App\Events\OrderCancelled;
use App\Events\OrderStatusChanged;
use App\Exceptions\OutOfStockException;
use App\Models\Pedido;
use App\Models\PedidoItem;
use App\Repositories\Contracts\PedidoRepositoryInterface;
use App\Repositories\Contracts\ProdutoRepositoryInterface;
use App\Repositories\Contracts\PedidoItemRepositoryInterface;
use App\Services\Contracts\OrderServiceInterface;
use App\Services\Contracts\StockServiceInterface;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Pagination\LengthAwarePaginator;

class OrderService implements OrderServiceInterface
{
    /**
     * Mapeamento de status para cores e ícones
     */
    protected array $statusMap = [
        'pendente' => [
            'label' => 'Pendente',
            'color' => 'warning',
            'icon' => 'fa-clock',
            'badge' => 'bg-warning'
        ],
        'pago' => [
            'label' => 'Pago',
            'color' => 'info',
            'icon' => 'fa-check-circle',
            'badge' => 'bg-info'
        ],
        'processando' => [
            'label' => 'Processando',
            'color' => 'primary',
            'icon' => 'fa-spinner',
            'badge' => 'bg-primary'
        ],
        'enviado' => [
            'label' => 'Enviado',
            'color' => 'success',
            'icon' => 'fa-truck',
            'badge' => 'bg-success'
        ],
        'entregue' => [
            'label' => 'Entregue',
            'color' => 'success',
            'icon' => 'fa-check-double',
            'badge' => 'bg-success'
        ],
        'cancelado' => [
            'label' => 'Cancelado',
            'color' => 'danger',
            'icon' => 'fa-times-circle',
            'badge' => 'bg-danger'
        ],
    ];

    public function __construct(
        protected PedidoRepositoryInterface $pedidoRepository,
        protected ProdutoRepositoryInterface $produtoRepository,
        protected PedidoItemRepositoryInterface $pedidoItemRepository,
        protected StockServiceInterface $stockService
    ) {}

    /**
     * {@inheritdoc}
     */
    public function createOrder(OrderDTO $dto, array $carrinho): OrderResponseDTO
    {
        return $this->pedidoRepository->transaction(function() use ($dto, $carrinho) {
            // Validar estoque de todos os itens
            $this->validateOrder($carrinho);
            
            // Calcular valores
            $subtotal = $this->calculateSubtotal($carrinho);
            $desconto = $this->calculateDiscount($carrinho, $subtotal);
            $total = $subtotal - $desconto;
            
            // Gerar número do pedido
            $numeroPedido = $this->pedidoRepository->generateNumero();
            
            // Criar pedido via Repository
            $pedidoData = [
                'user_id' => Auth::id(),
                'numero_pedido' => $numeroPedido,
                'subtotal' => $subtotal,
                'desconto' => $desconto,
                'total' => $total,
                'status' => 'pendente',
                'forma_pagamento' => $dto->forma_pagamento,
                'status_pagamento' => 'aguardando',
                'observacoes' => $dto->observacoes,
                'endereco_entrega' => $dto->endereco,
                'cidade' => $dto->cidade,
                'estado' => $dto->estado,
                'cep' => $dto->cep,
                'telefone' => $dto->telefone ?? null,
            ];
            
            $pedido = $this->pedidoRepository->create($pedidoData);
            
            // Criar itens do pedido e reservar estoque
            $this->createOrderItems($pedido, $carrinho);
            
            // Reservar estoque
            $this->reserveStockForItems($carrinho);
            
            // Disparar evento
            event(new OrderCreated($pedido));
            
            Log::info('Pedido criado com sucesso', [
                'pedido_id' => $pedido->id,
                'numero_pedido' => $numeroPedido,
                'user_id' => Auth::id(),
                'total' => $total,
                'itens' => count($carrinho)
            ]);
            
            return OrderResponseDTO::fromModel($pedido);
        });
    }

    /**
     * {@inheritdoc}
     */
    public function cancelOrder(Pedido $pedido): bool
    {
        return $this->pedidoRepository->transaction(function() use ($pedido) {
            // Verificar se pode cancelar
            if (!$this->canUserCancelOrder($pedido)) {
                throw new \Exception('Este pedido não pode ser cancelado. Status atual: ' . $pedido->status);
            }

            // Verificar se já está cancelado
            if ($pedido->status === 'cancelado') {
                throw new \Exception('Este pedido já está cancelado.');
            }

            // Restaurar estoque
            foreach ($pedido->itens as $item) {
                $produto = $this->produtoRepository->find($item->produto_id);
                if ($produto) {
                    $this->stockService->releaseStock($produto, $item->quantidade);
                }
            }

            // Atualizar status via Repository
            $this->pedidoRepository->updateStatus($pedido->id, 'cancelado');

            // Recarregar pedido
            $pedido->refresh();

            // Disparar evento
            event(new OrderCancelled($pedido));

            Log::info('Pedido cancelado com sucesso', [
                'pedido_id' => $pedido->id,
                'user_id' => Auth::id()
            ]);

            return true;
        });
    }

    /**
     * {@inheritdoc}
     */
    public function getUserOrders(int $userId, int $perPage = 10): LengthAwarePaginator
    {
        return $this->pedidoRepository->findByUser($userId, $perPage);
    }

    /**
     * {@inheritdoc}
     */
    public function getOrderDetails(int $orderId, int $userId): ?OrderResponseDTO
    {
        $pedido = $this->pedidoRepository->findWithItems($orderId);
        
        if (!$pedido || $pedido->user_id !== $userId) {
            return null;
        }

        return OrderResponseDTO::fromModel($pedido);
    }

    /**
     * {@inheritdoc}
     */
    public function refundOrder(Pedido $pedido): bool
    {
        return $this->pedidoRepository->transaction(function() use ($pedido) {
            // Verificar se pode reembolsar
            if (!in_array($pedido->status, ['pago', 'entregue'])) {
                throw new \Exception('Este pedido não pode ser reembolsado.');
            }

            // Verificar se já foi reembolsado
            if ($pedido->status_pagamento === 'refunded') {
                throw new \Exception('Este pedido já foi reembolsado.');
            }

            // Atualizar status
            $this->pedidoRepository->update($pedido->id, [
                'status' => 'cancelado',
                'status_pagamento' => 'refunded'
            ]);

            // Restaurar estoque
            foreach ($pedido->itens as $item) {
                $produto = $this->produtoRepository->find($item->produto_id);
                if ($produto) {
                    $this->stockService->releaseStock($produto, $item->quantidade);
                }
            }

            $pedido->refresh();

            Log::info('Pedido reembolsado', [
                'pedido_id' => $pedido->id,
                'user_id' => Auth::id()
            ]);

            return true;
        });
    }

    /**
     * {@inheritdoc}
     */
    public function calculateSubtotal(array $carrinho): float
    {
        $subtotal = 0;
        foreach ($carrinho as $item) {
            $preco = $item['preco'] ?? $item['preco_unitario'] ?? 0;
            $quantidade = $item['quantidade'] ?? 1;
            $subtotal += $preco * $quantidade;
        }
        return round($subtotal, 2);
    }

    /**
     * {@inheritdoc}
     */
    public function calculateDiscount(array $carrinho, float $subtotal): float
    {
        $desconto = 0;

        // Verificar se há itens com preço promocional
        foreach ($carrinho as $item) {
            if (isset($item['preco_promocional']) && $item['preco_promocional'] > 0) {
                $economia = ($item['preco'] - $item['preco_promocional']) * $item['quantidade'];
                $desconto += $economia;
            }
        }

        // Desconto por valor total (frete grátis)
        if ($subtotal >= 100) {
            // Já tem desconto de frete
        }

        return round($desconto, 2);
    }

    /**
     * {@inheritdoc}
     */
    public function validateOrder(array $carrinho): void
    {
        if (empty($carrinho)) {
            throw new \Exception('O carrinho está vazio.');
        }

        foreach ($carrinho as $id => $item) {
            $produto = $this->produtoRepository->find($id);
            
            if (!$produto) {
                throw new \Exception("Produto não encontrado: {$id}");
            }

            $quantidade = $item['quantidade'] ?? 1;
            
            if (!$this->stockService->validateStock($produto, $quantidade)) {
                throw new OutOfStockException(
                    "Produto '{$produto->descricao}' não tem estoque suficiente. " .
                    "Disponível: {$produto->quantidade}, Solicitado: {$quantidade}"
                );
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getOrderStatusInfo(Pedido $pedido): array
    {
        $status = $pedido->status;
        
        if (isset($this->statusMap[$status])) {
            return $this->statusMap[$status];
        }

        return [
            'label' => ucfirst($status),
            'color' => 'secondary',
            'icon' => 'fa-circle',
            'badge' => 'bg-secondary'
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function canUserCancelOrder(Pedido $pedido): bool
    {
        return $this->pedidoRepository->canBeCancelled($pedido->id);
    }

    /**
     * {@inheritdoc}
     */
    public function getUserOrderStats(int $userId): array
    {
        $stats = [
            'total' => 0,
            'pendentes' => 0,
            'processando' => 0,
            'enviados' => 0,
            'entregues' => 0,
            'cancelados' => 0,
        ];

        $pedidos = $this->pedidoRepository->findByUser($userId, 9999);
        
        foreach ($pedidos as $pedido) {
            $stats['total']++;
            
            switch ($pedido->status) {
                case 'pendente':
                    $stats['pendentes']++;
                    break;
                case 'processando':
                    $stats['processando']++;
                    break;
                case 'enviado':
                    $stats['enviados']++;
                    break;
                case 'entregue':
                    $stats['entregues']++;
                    break;
                case 'cancelado':
                    $stats['cancelados']++;
                    break;
            }
        }

        return $stats;
    }

    /**
     * {@inheritdoc}
     */
    public function getOrderByNumber(string $numero): ?OrderResponseDTO
    {
        $pedido = $this->pedidoRepository->findByNumero($numero);
        
        if (!$pedido) {
            return null;
        }

        return OrderResponseDTO::fromModel($pedido);
    }

    /**
     * {@inheritdoc}
     */
    public function updateOrderStatus(int $orderId, string $status, array $extra = []): OrderResponseDTO
    {
        return $this->pedidoRepository->transaction(function() use ($orderId, $status, $extra) {
            // Verificar se o pedido existe
            $pedido = $this->pedidoRepository->find($orderId);
            
            if (!$pedido) {
                throw new \Exception("Pedido não encontrado: {$orderId}");
            }

            // Validar transição de status
            $this->validateStatusTransition($pedido, $status);

            // Atualizar status
            $pedido = $this->pedidoRepository->updateStatus($orderId, $status, $extra);

            // Disparar evento
            event(new OrderStatusChanged($pedido));

            Log::info('Status do pedido atualizado', [
                'pedido_id' => $orderId,
                'novo_status' => $status,
                'user_id' => Auth::id()
            ]);

            return OrderResponseDTO::fromModel($pedido);
        });
    }

    /**
     * {@inheritdoc}
     */
    public function getPendingOrders(int $limit = null): LengthAwarePaginator
    {
        $filters = ['status' => 'pendente'];
        return $this->pedidoRepository->getFilteredOrders($filters, $limit ?? 15);
    }

    /**
     * {@inheritdoc}
     */
    public function getOrdersByDateRange(string $startDate, string $endDate): array
    {
        $pedidos = $this->pedidoRepository->findByDateRange($startDate, $endDate);
        
        return [
            'pedidos' => $pedidos,
            'total' => $pedidos->count(),
            'total_vendas' => $pedidos->sum('total'),
            'ticket_medio' => $pedidos->count() > 0 ? $pedidos->sum('total') / $pedidos->count() : 0,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getLastOrder(int $userId): ?OrderResponseDTO
    {
        $pedidos = $this->pedidoRepository->findByUser($userId, 1);
        
        if ($pedidos->isEmpty()) {
            return null;
        }

        return OrderResponseDTO::fromModel($pedidos->first());
    }

    /**
     * {@inheritdoc}
     */
    public function hasPendingOrder(int $userId): bool
    {
        $pedidos = $this->pedidoRepository->findByUser($userId, 9999);
        
        foreach ($pedidos as $pedido) {
            if (in_array($pedido->status, ['pendente', 'processando', 'pago'])) {
                return true;
            }
        }
        
        return false;
    }

    // ============================================
    // MÉTODOS PRIVADOS
    // ============================================

    /**
     * Criar itens do pedido
     */
    protected function createOrderItems(Pedido $pedido, array $carrinho): void
    {
        foreach ($carrinho as $id => $item) {
            $produto = $this->produtoRepository->find($id);
            
            if (!$produto) {
                throw new \Exception("Produto não encontrado: {$id}");
            }

            $quantidade = $item['quantidade'] ?? 1;
            $precoUnitario = $item['preco'] ?? $item['preco_unitario'] ?? $produto->preco;
            $precoPromocional = $item['preco_promocional'] ?? $produto->preco_promocional ?? null;
            $subtotal = $precoUnitario * $quantidade;

            $this->pedidoItemRepository->create([
                'pedido_id' => $pedido->id,
                'produto_id' => $id,
                'quantidade' => $quantidade,
                'preco_unitario' => $precoUnitario,
                'preco_promocional' => $precoPromocional,
                'subtotal' => $subtotal,
                'nome_produto' => $produto->descricao,
                'imagem_produto' => $produto->imagem
            ]);
        }
    }

    /**
     * Reservar estoque para os itens
     */
    protected function reserveStockForItems(array $carrinho): void
    {
        foreach ($carrinho as $id => $item) {
            $produto = $this->produtoRepository->find($id);
            if ($produto) {
                $quantidade = $item['quantidade'] ?? 1;
                $this->stockService->reserveStock($produto, $quantidade);
            }
        }
    }

    /**
     * Validar transição de status
     */
    protected function validateStatusTransition(Pedido $pedido, string $novoStatus): void
    {
        $statusAnterior = $pedido->status;

        // Regras de transição
        $transitions = [
            'pendente' => ['pago', 'processando', 'cancelado'],
            'pago' => ['processando', 'enviado', 'cancelado'],
            'processando' => ['enviado', 'cancelado'],
            'enviado' => ['entregue', 'cancelado'],
            'entregue' => [], // Não pode mais mudar
            'cancelado' => [], // Não pode mais mudar
        ];

        // Verificar se a transição é permitida
        if (!isset($transitions[$statusAnterior]) || !in_array($novoStatus, $transitions[$statusAnterior])) {
            throw new \Exception(
                "Transição de status inválida: de '{$statusAnterior}' para '{$novoStatus}'"
            );
        }

        // Regras especiais
        if ($novoStatus === 'cancelado') {
            if (!$this->canUserCancelOrder($pedido)) {
                throw new \Exception('Este pedido não pode ser cancelado.');
            }
        }
    }

    /**
     * Verificar se o pedido pode ser reembolsado
     */
    protected function canRefundOrder(Pedido $pedido): bool
    {
        return in_array($pedido->status, ['pago', 'entregue']) 
            && $pedido->status_pagamento !== 'refunded';
    }

    /**
     * Obter próximo status possível
     */
    protected function getNextStatuses(Pedido $pedido): array
    {
        $statuses = [
            'pendente' => ['pago', 'processando', 'cancelado'],
            'pago' => ['processando', 'enviado', 'cancelado'],
            'processando' => ['enviado', 'cancelado'],
            'enviado' => ['entregue', 'cancelado'],
            'entregue' => [],
            'cancelado' => [],
        ];

        return $statuses[$pedido->status] ?? [];
    }

    /**
     * Calcular frete
     */
    protected function calculateShipping(array $carrinho, float $subtotal): float
    {
        // Frete grátis acima de R$ 100
        if ($subtotal >= 100) {
            return 0;
        }

        // Frete fixo para compras menores
        return 15.00;
    }

    /**
     * Gerar resumo do pedido para log
     */
    protected function generateOrderSummary(Pedido $pedido): array
    {
        return [
            'id' => $pedido->id,
            'numero' => $pedido->numero_pedido,
            'total' => $pedido->total,
            'status' => $pedido->status,
            'itens' => $pedido->itens->count(),
            'usuario' => $pedido->user->name ?? 'N/A',
            'data' => $pedido->created_at->format('d/m/Y H:i'),
        ];
    }

    /**
     * Validar se o pedido pertence ao usuário
     */
    protected function validateOrderOwnership(Pedido $pedido, int $userId): void
    {
        if ($pedido->user_id !== $userId) {
            throw new \Exception('Você não tem permissão para acessar este pedido.');
        }
    }

    /**
     * Calcular totais completos do pedido
     */
    protected function calculateTotals(array $carrinho): array
    {
        $subtotal = $this->calculateSubtotal($carrinho);
        $desconto = $this->calculateDiscount($carrinho, $subtotal);
        $frete = $this->calculateShipping($carrinho, $subtotal);
        $total = $subtotal - $desconto + $frete;

        return [
            'subtotal' => $subtotal,
            'desconto' => $desconto,
            'frete' => $frete,
            'total' => $total,
        ];
    }

    /**
     * Verificar se o pedido está em um status que permite pagamento
     */
    protected function canPayOrder(Pedido $pedido): bool
    {
        return in_array($pedido->status, ['pendente', 'processando']);
    }

    /**
     * Obter status para exibição no frontend
     */
    protected function getStatusBadge(string $status): string
    {
        $map = [
            'pendente' => '<span class="badge bg-warning">Pendente</span>',
            'pago' => '<span class="badge bg-info">Pago</span>',
            'processando' => '<span class="badge bg-primary">Processando</span>',
            'enviado' => '<span class="badge bg-success">Enviado</span>',
            'entregue' => '<span class="badge bg-success">Entregue</span>',
            'cancelado' => '<span class="badge bg-danger">Cancelado</span>',
        ];

        return $map[$status] ?? '<span class="badge bg-secondary">' . ucfirst($status) . '</span>';
    }

    /**
     * Verificar se o pedido pode ser alterado
     */
    protected function isOrderModifiable(Pedido $pedido): bool
    {
        return !in_array($pedido->status, ['entregue', 'cancelado']);
    }

    /**
     * Obter total de itens do pedido
     */
    protected function getTotalItems(Pedido $pedido): int
    {
        return $pedido->itens->sum('quantidade') ?? 0;
    }

    /**
     * Obter valor total dos produtos (sem frete e desconto)
     */
    protected function getProductsTotal(Pedido $pedido): float
    {
        return $pedido->itens->sum('subtotal') ?? 0;
    }
}