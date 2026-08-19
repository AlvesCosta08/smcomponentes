@extends('layouts.app')

@section('title', 'Gerenciar Pedidos - Admin')

@section('content')
<div class="container-fluid px-4">
    <div class="page-header d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0"><i class="bi bi-box-seam"></i> Gerenciar Pedidos</h1>
            <small class="text-muted">Visualize e gerencie todos os pedidos da loja</small>
        </div>
        <div>
            <a href="{{ route('admin.pedidos.export') }}" class="btn btn-success">
                <i class="bi bi-file-excel"></i> Exportar CSV
            </a>
        </div>
    </div>

    <!-- Cards de Estatísticas -->
    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="admin-stat-card">
                <div class="stat-number">{{ $totalPedidos ?? 0 }}</div>
                <div class="stat-label">Total de Pedidos</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="admin-stat-card border-warning">
                <div class="stat-number text-warning">{{ $pedidosPendentes ?? 0 }}</div>
                <div class="stat-label">Pendentes</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="admin-stat-card border-success">
                <div class="stat-number text-success">R$ {{ number_format($totalFaturado ?? 0, 2, ',', '.') }}</div>
                <div class="stat-label">Total Faturado</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="admin-stat-card border-info">
                <div class="stat-number text-info">{{ $pedidosHoje ?? 0 }}</div>
                <div class="stat-label">Pedidos Hoje</div>
            </div>
        </div>
    </div>

    <!-- Filtros -->
    <div class="admin-card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.pedidos.index') }}" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="">Todos</option>
                        @php
                            $statusList = [
                                'pendente' => 'Pendente',
                                'pago' => 'Pago',
                                'processando' => 'Processando',
                                'enviado' => 'Enviado',
                                'entregue' => 'Entregue',
                                'cancelado' => 'Cancelado'
                            ];
                        @endphp
                        @foreach($statusList as $key => $label)
                            <option value="{{ $key }}" {{ request('status') == $key ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Data Início</label>
                    <input type="date" name="data_inicio" class="form-control" value="{{ request('data_inicio') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Data Fim</label>
                    <input type="date" name="data_fim" class="form-control" value="{{ request('data_fim') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Buscar</label>
                    <input type="text" name="search" class="form-control" placeholder="Número do pedido..." value="{{ request('search') }}">
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-search"></i> Filtrar
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Lista de Pedidos -->
    <div class="admin-card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover admin-table mb-0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Pedido</th>
                            <th>Cliente</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Data</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($pedidos ?? [] as $pedido)
                            <tr>
                                <td>{{ $pedido->id }}</td>
                                <td>
                                    <strong>{{ $pedido->numero_pedido }}</strong>
                                </td>
                                <td>{{ $pedido->user->name ?? 'N/A' }}</td>
                                <td class="fw-bold">R$ {{ number_format($pedido->total, 2, ',', '.') }}</td>
                                <td>
                                    @php
                                        $colors = ['pendente' => 'warning', 'pago' => 'info', 'processando' => 'primary', 'enviado' => 'success', 'entregue' => 'success', 'cancelado' => 'danger'];
                                        $labels = ['pendente' => 'Pendente', 'pago' => 'Pago', 'processando' => 'Processando', 'enviado' => 'Enviado', 'entregue' => 'Entregue', 'cancelado' => 'Cancelado'];
                                    @endphp
                                    <span class="badge-status bg-{{ $colors[$pedido->status] ?? 'secondary' }} text-white">
                                        {{ $labels[$pedido->status] ?? $pedido->status }}
                                    </span>
                                </td>
                                <td>{{ $pedido->created_at->format('d/m/Y H:i') }}</td>
                                <td>
                                    <a href="{{ route('admin.pedidos.show', $pedido) }}" 
                                       class="btn btn-sm btn-primary">
                                        <i class="bi bi-eye"></i> Ver
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">
                                    <i class="bi bi-inbox display-4 d-block"></i>
                                    Nenhum pedido encontrado.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer">
            {{ $pedidos->withQueryString()->links() ?? '' }}
        </div>
    </div>
</div>
@endsection