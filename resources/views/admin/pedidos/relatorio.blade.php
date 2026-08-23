@extends('layouts.app')

@section('title', 'Relatório de Pedidos - Admin')

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h1 class="h2 mb-0">📊 Relatório de Pedidos</h1>
            <small class="text-muted">Análise de vendas e pedidos</small>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <a href="{{ route('admin.pedidos.index') }}" class="btn btn-secondary btn-sm">
                <i class="bi bi-arrow-left"></i> Voltar
            </a>
            <button onclick="window.print()" class="btn btn-primary btn-sm">
                <i class="bi bi-printer"></i> Imprimir
            </button>
            <a href="{{ route('admin.pedidos.export') }}" class="btn btn-success btn-sm">
                <i class="bi bi-file-excel"></i> Exportar
            </a>
        </div>
    </div>

    <!-- Filtros -->
    <div class="admin-card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.pedidos.relatorio') }}" class="row g-3">
                <div class="col-md-4">
                    <label for="data_inicio" class="form-label">Data Início</label>
                    <input type="date" name="data_inicio" id="data_inicio" 
                           class="form-control" value="{{ $dataInicio ?? now()->startOfMonth()->format('Y-m-d') }}">
                </div>
                <div class="col-md-4">
                    <label for="data_fim" class="form-label">Data Fim</label>
                    <input type="date" name="data_fim" id="data_fim" 
                           class="form-control" value="{{ $dataFim ?? now()->format('Y-m-d') }}">
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-filter"></i> Filtrar
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Cards de Resumo -->
    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="admin-stat-card bg-primary text-white">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="stat-label text-white-50">Total de Pedidos</div>
                        <div class="stat-number fs-1">{{ $totalPedidos ?? 0 }}</div>
                    </div>
                    <div class="stat-icon bg-white bg-opacity-25 text-white">
                        <i class="bi bi-cart-fill"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="admin-stat-card bg-success text-white">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="stat-label text-white-50">Faturamento Total</div>
                        <div class="stat-number fs-1">R$ {{ number_format($totalVendas ?? 0, 2, ',', '.') }}</div>
                    </div>
                    <div class="stat-icon bg-white bg-opacity-25 text-white">
                        <i class="bi bi-currency-dollar"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="admin-stat-card bg-info text-white">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="stat-label text-white-50">Ticket Médio</div>
                        <div class="stat-number fs-1">R$ {{ number_format($mediaTicket ?? 0, 2, ',', '.') }}</div>
                    </div>
                    <div class="stat-icon bg-white bg-opacity-25 text-white">
                        <i class="bi bi-receipt"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Gráfico de Vendas por Dia -->
    @if(isset($vendasPorDia) && count($vendasPorDia) > 0)
    <div class="admin-card mb-4">
        <div class="card-header">
            <span><i class="bi bi-graph-up me-2 text-primary"></i>Vendas por Dia (Últimos 30 dias)</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover admin-table mb-0">
                    <thead>
                        <tr>
                            <th>Data</th>
                            <th>Quantidade de Pedidos</th>
                            <th>Total Vendido</th>
                            <th>Ticket Médio</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($vendasPorDia as $dia)
                            <tr>
                                <td>{{ \Carbon\Carbon::parse($dia['data'])->format('d/m/Y') }}</td>
                                <td>{{ $dia['quantidade'] }}</td>
                                <td class="fw-bold text-success">
                                    R$ {{ number_format($dia['total'], 2, ',', '.') }}
                                </td>
                                <td>
                                    R$ {{ number_format($dia['quantidade'] > 0 ? $dia['total'] / $dia['quantidade'] : 0, 2, ',', '.') }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif

    <!-- Vendas por Mês -->
    @if(isset($vendasPorMes) && $vendasPorMes->count() > 0)
    <div class="admin-card mb-4">
        <div class="card-header">
            <span><i class="bi bi-graph-up-arrow me-2 text-primary"></i>Vendas por Mês</span>
        </div>
        <div class="card-body">
            <div class="row g-2">
                @foreach($vendasPorMes as $venda)
                    <div class="col-4 col-md-2 text-center">
                        <div class="p-2 bg-light rounded">
                            <small class="text-muted d-block">{{ $venda->mes }}</small>
                            <strong class="text-primary">R$ {{ number_format($venda->total, 2, ',', '.') }}</strong>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif

    <!-- Top Produtos -->
    @if(isset($topProdutos) && $topProdutos->count() > 0)
    <div class="admin-card mb-4">
        <div class="card-header">
            <span><i class="bi bi-trophy me-2 text-warning"></i>Top 10 Produtos Mais Vendidos</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover admin-table mb-0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Produto</th>
                            <th>Quantidade</th>
                            <th>Total Vendido</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($topProdutos as $index => $produto)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $produto->nome_produto ?? 'Produto #' . $produto->produto_id }}</td>
                                <td>{{ number_format($produto->total_quantidade, 0, ',', '.') }}</td>
                                <td class="fw-bold text-success">
                                    R$ {{ number_format($produto->total_vendido, 2, ',', '.') }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif

    <!-- Lista de Pedidos -->
    <div class="admin-card">
        <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
            <span><i class="bi bi-list-ul me-2 text-primary"></i>Pedidos no Período</span>
            <span class="badge bg-secondary">{{ $totalPedidos ?? 0 }} pedidos</span>
        </div>
        <div class="card-body p-0">
            @if(isset($pedidos) && $pedidos->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover admin-table mb-0">
                        <thead>
                            <tr>
                                <th># Pedido</th>
                                <th>Cliente</th>
                                <th>Data</th>
                                <th>Total</th>
                                <th>Status</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($pedidos as $pedido)
                                <tr>
                                    <td>
                                        <strong>#{{ $pedido->numero_pedido ?? $pedido->id }}</strong>
                                    </td>
                                    <td>{{ $pedido->user->name ?? 'Cliente #' . $pedido->user_id }}</td>
                                    <td>{{ $pedido->created_at->format('d/m/Y H:i') }}</td>
                                    <td class="fw-bold text-success">
                                        R$ {{ number_format($pedido->total, 2, ',', '.') }}
                                    </td>
                                    <td>
                                        @php
                                            $colors = ['pendente' => 'warning', 'pago' => 'info', 'processando' => 'primary', 'enviado' => 'success', 'entregue' => 'success', 'cancelado' => 'danger'];
                                            $labels = ['pendente' => 'Pendente', 'pago' => 'Pago', 'processando' => 'Processando', 'enviado' => 'Enviado', 'entregue' => 'Entregue', 'cancelado' => 'Cancelado'];
                                        @endphp
                                        <span class="badge bg-{{ $colors[$pedido->status] ?? 'secondary' }}">
                                            {{ $labels[$pedido->status] ?? $pedido->status }}
                                        </span>
                                    </td>
                                    <td>
                                        <a href="{{ route('admin.pedidos.show', $pedido) }}" 
                                           class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="p-3 border-top">
                    {{ $pedidos->links('pagination::bootstrap-5') }}
                </div>
            @else
                <div class="text-center py-5">
                    <i class="bi bi-inbox fs-1 d-block mb-3 text-muted"></i>
                    <p class="text-muted fs-5">Nenhum pedido encontrado no período selecionado.</p>
                    <small class="text-muted">Tente ajustar o filtro de datas.</small>
                </div>
            @endif
        </div>
    </div>
</div>

<style>
    .admin-stat-card {
        padding: 20px;
        border-radius: 12px;
        transition: all 0.2s ease;
    }
    .admin-stat-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 25px rgba(0,0,0,0.15);
    }
    .stat-number {
        font-size: 2rem;
        font-weight: 700;
    }
    .stat-icon {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
    }
    .admin-card {
        background: #fff;
        border-radius: 12px;
        border: 1px solid #e9ecef;
        overflow: hidden;
    }
    .admin-card .card-header {
        background: #f8f9fa;
        padding: 12px 20px;
        border-bottom: 1px solid #e9ecef;
        font-weight: 600;
    }
    .admin-card .card-body {
        padding: 20px;
    }
    .admin-table th {
        background: #f8f9fa;
        font-weight: 600;
        font-size: 0.85rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #6c757d;
    }
    .admin-table td {
        vertical-align: middle;
        padding: 10px 12px;
    }
    @media print {
        .btn, .admin-card .card-header .badge {
            display: none !important;
        }
        .admin-card {
            border: none !important;
            box-shadow: none !important;
        }
        .admin-stat-card {
            border: 1px solid #ddd !important;
        }
    }
</style>
@endsection