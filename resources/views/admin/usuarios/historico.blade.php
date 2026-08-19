@extends('layouts.app')

@section('title', 'Histórico de Pedidos - Admin')

@section('content')
<div class="container-fluid px-4">
    <div class="page-header d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0"><i class="bi bi-clock-history"></i> Histórico de Pedidos</h1>
            <small class="text-muted">Usuário: {{ $usuario->name }}</small>
        </div>
        <div>
            <a href="{{ route('admin.usuarios.show', $usuario) }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Voltar
            </a>
        </div>
    </div>

    <div class="admin-card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover admin-table mb-0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Pedido</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Data</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($pedidos as $pedido)
                            <tr>
                                <td>{{ $pedido->id }}</td>
                                <td>
                                    <strong>{{ $pedido->numero_pedido }}</strong>
                                </td>
                                <td>R$ {{ number_format($pedido->total, 2, ',', '.') }}</td>
                                <td>
                                    @php
                                        $colors = ['pendente' => 'warning', 'pago' => 'info', 'processando' => 'primary', 'enviado' => 'success', 'entregue' => 'success', 'cancelado' => 'danger'];
                                        $labels = ['pendente' => 'Pendente', 'pago' => 'Pago', 'processando' => 'Processando', 'enviado' => 'Enviado', 'entregue' => 'Entregue', 'cancelado' => 'Cancelado'];
                                    @endphp
                                    <span class="badge bg-{{ $colors[$pedido->status] ?? 'secondary' }}">
                                        {{ $labels[$pedido->status] ?? $pedido->status }}
                                    </span>
                                </td>
                                <td>{{ $pedido->created_at->format('d/m/Y H:i') }}</td>
                                <td>
                                    <a href="{{ route('admin.pedidos.show', $pedido) }}" class="btn btn-sm btn-primary">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-4">
                                    <i class="bi bi-inbox display-4 d-block text-muted"></i>
                                    <span class="text-muted">Nenhum pedido encontrado.</span>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer">
            {{ $pedidos->links() }}
        </div>
    </div>
</div>
@endsection