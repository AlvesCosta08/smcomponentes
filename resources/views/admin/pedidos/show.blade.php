@extends('layouts.app')

@section('title', 'Pedido #' . $pedido->numero_pedido)

@section('content')
<div class="container-fluid px-4">
    <div class="page-header d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0"><i class="bi bi-box-seam"></i> Pedido #{{ $pedido->numero_pedido }}</h1>
            <small class="text-muted">Detalhes completos do pedido</small>
        </div>
        <div>
            <a href="{{ route('admin.pedidos.index') }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Voltar
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            <i class="bi bi-check-circle"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="bi bi-exclamation-triangle"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row g-4">
        <!-- Itens do Pedido -->
        <div class="col-lg-8">
            <div class="admin-card">
                <div class="card-header bg-dark text-white">
                    <h5 class="mb-0"><i class="bi bi-list-check"></i> Itens do Pedido</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover admin-table mb-0">
                            <thead>
                                <tr>
                                    <th>Produto</th>
                                    <th>Qtd</th>
                                    <th>Preço Unit.</th>
                                    <th>Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($pedido->itens as $item)
                                    <tr>
                                        <td>
                                            <strong>{{ $item->nome_produto ?? $item->produto->descricao ?? 'N/A' }}</strong>
                                            <br>
                                            <small class="text-muted">Ref: {{ $item->produto->referencia ?? '-' }}</small>
                                        </td>
                                        <td>{{ $item->quantidade }}</td>
                                        <td>R$ {{ number_format($item->preco_unitario, 2, ',', '.') }}</td>
                                        <td class="fw-bold">R$ {{ number_format($item->subtotal, 2, ',', '.') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="table-light">
                                <tr>
                                    <td colspan="3" class="text-end fw-bold">Subtotal:</td>
                                    <td>R$ {{ number_format($pedido->subtotal, 2, ',', '.') }}</td>
                                </tr>
                                @if($pedido->desconto > 0)
                                <tr>
                                    <td colspan="3" class="text-end text-success">Desconto:</td>
                                    <td>- R$ {{ number_format($pedido->desconto, 2, ',', '.') }}</td>
                                </tr>
                                @endif
                                <tr class="fw-bold fs-5">
                                    <td colspan="3" class="text-end">Total:</td>
                                    <td class="text-primary">R$ {{ number_format($pedido->total, 2, ',', '.') }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Informações do Cliente e Status -->
        <div class="col-lg-4">
            <!-- Status -->
            <div class="admin-card mb-4">
                <div class="card-header bg-dark text-white">
                    <h5 class="mb-0"><i class="bi bi-arrow-repeat"></i> Status</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.pedidos.status', $pedido) }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="mb-3">
                            <label class="form-label">Alterar Status</label>
                            @php
                                $statusList = [
                                    'pendente' => 'Pendente',
                                    'pago' => 'Pago',
                                    'processando' => 'Processando',
                                    'enviado' => 'Enviado',
                                    'entregue' => 'Entregue',
                                    'cancelado' => 'Cancelado'
                                ];
                                $colors = ['pendente' => 'warning', 'pago' => 'info', 'processando' => 'primary', 'enviado' => 'success', 'entregue' => 'success', 'cancelado' => 'danger'];
                            @endphp
                            <select name="status" class="form-select">
                                @foreach($statusList as $key => $label)
                                    <option value="{{ $key }}" 
                                        {{ $pedido->status == $key ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-arrow-clockwise"></i> Atualizar Status
                        </button>
                    </form>

                    <hr>
                    
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-muted">Status Atual:</span>
                        <span class="badge-status bg-{{ $colors[$pedido->status] ?? 'secondary' }} text-white fs-6">
                            {{ $statusList[$pedido->status] ?? $pedido->status }}
                        </span>
                    </div>
                    
                    @if($pedido->data_pagamento)
                    <div class="mt-2 d-flex justify-content-between">
                        <span class="text-muted">Data Pagamento:</span>
                        <span>{{ $pedido->data_pagamento->format('d/m/Y H:i') }}</span>
                    </div>
                    @endif
                    
                    @if($pedido->data_envio)
                    <div class="mt-1 d-flex justify-content-between">
                        <span class="text-muted">Data Envio:</span>
                        <span>{{ $pedido->data_envio->format('d/m/Y H:i') }}</span>
                    </div>
                    @endif
                    
                    @if($pedido->data_entrega)
                    <div class="mt-1 d-flex justify-content-between">
                        <span class="text-muted">Data Entrega:</span>
                        <span>{{ $pedido->data_entrega->format('d/m/Y H:i') }}</span>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Informações do Cliente -->
            <div class="admin-card">
                <div class="card-header bg-dark text-white">
                    <h5 class="mb-0"><i class="bi bi-person"></i> Cliente</h5>
                </div>
                <div class="card-body">
                    <p class="mb-1">
                        <strong>Nome:</strong><br>
                        {{ $pedido->user->name ?? 'N/A' }}
                    </p>
                    <p class="mb-1">
                        <strong>Email:</strong><br>
                        {{ $pedido->user->email ?? 'N/A' }}
                    </p>
                    <p class="mb-1">
                        <strong>Telefone:</strong><br>
                        {{ $pedido->user->telefone ?? 'N/A' }}
                    </p>
                    <hr>
                    <p class="mb-1">
                        <strong>Data do Pedido:</strong><br>
                        {{ $pedido->created_at->format('d/m/Y H:i') }}
                    </p>
                    <p class="mb-1">
                        <strong>Forma de Pagamento:</strong><br>
                        {{ ucfirst($pedido->forma_pagamento ?? 'N/A') }}
                    </p>
                    <p class="mb-1">
                        <strong>Status Pagamento:</strong><br>
                        <span class="badge bg-{{ $pedido->status_pagamento == 'pago' ? 'success' : 'warning' }}">
                            {{ ucfirst($pedido->status_pagamento ?? 'N/A') }}
                        </span>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection