@extends('layouts.app')

@section('title', 'Detalhes do Pedido #' . ($pedido->numero_pedido ?? $pedido->id))

@section('content')
<div class="container py-4">
    <div class="row">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('cliente.pedidos.index') }}">Meus Pedidos</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Pedido #{{ $pedido->numero_pedido ?? $pedido->id }}</li>
                </ol>
            </nav>
            
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <h1 class="display-5 fw-bold">📦 Pedido #{{ $pedido->numero_pedido ?? $pedido->id }}</h1>
                    <p class="text-muted">
                        Realizado em {{ $pedido->created_at->format('d/m/Y H:i') }}
                    </p>
                </div>
                <div>
                    {{-- ✅ CORRIGIDO: Usar $pedido->status->label() em vez de ucfirst($pedido->status) --}}
                    @php
                        $statusValue = $pedido->status->value ?? 'pendente';
                        $statusLabel = $pedido->status->label() ?? ucfirst($statusValue);
                        $statusColor = match($statusValue) {
                            'entregue' => 'success',
                            'pendente' => 'warning',
                            'cancelado' => 'danger',
                            default => 'info'
                        };
                    @endphp
                    <span class="badge bg-{{ $statusColor }} fs-6 p-3">
                        {{ $statusLabel }}
                    </span>
                </div>
            </div>
            <hr>
        </div>
    </div>

    <div class="row g-4">
        <!-- Informações do Pedido -->
        <div class="col-md-4">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white border-bottom-0">
                    <h5 class="mb-0"><i class="bi bi-info-circle me-2 text-primary"></i>Informações</h5>
                </div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-sm-5">Número do Pedido</dt>
                        <dd class="col-sm-7">{{ $pedido->numero_pedido ?? $pedido->id }}</dd>
                        
                        <dt class="col-sm-5">Data</dt>
                        <dd class="col-sm-7">{{ $pedido->created_at->format('d/m/Y H:i') }}</dd>
                        
                        <dt class="col-sm-5">Status</dt>
                        <dd class="col-sm-7">
                            {{-- ✅ CORRIGIDO: Usar $pedido->status->label() --}}
                            @php
                                $statusValue = $pedido->status->value ?? 'pendente';
                                $statusLabel = $pedido->status->label() ?? ucfirst($statusValue);
                                $statusColor = match($statusValue) {
                                    'entregue' => 'success',
                                    'pendente' => 'warning',
                                    'cancelado' => 'danger',
                                    'pago' => 'success',
                                    'processando' => 'info',
                                    'enviado' => 'primary',
                                    default => 'secondary'
                                };
                            @endphp
                            <span class="badge bg-{{ $statusColor }}">
                                {{ $statusLabel }}
                            </span>
                        </dd>
                        
                        @if($pedido->status_pagamento)
                        <dt class="col-sm-5">Pagamento</dt>
                        <dd class="col-sm-7">
                            {{-- ✅ CORRIGIDO: Usar $pedido->status_pagamento->label() --}}
                            @php
                                $pagamentoValue = $pedido->status_pagamento->value ?? 'aguardando';
                                $pagamentoLabel = $pedido->status_pagamento->label() ?? ucfirst($pagamentoValue);
                                $pagamentoColor = match($pagamentoValue) {
                                    'aprovado' => 'success',
                                    'aguardando' => 'warning',
                                    'recusado' => 'danger',
                                    'cancelado' => 'danger',
                                    'estornado' => 'secondary',
                                    default => 'info'
                                };
                            @endphp
                            <span class="badge bg-{{ $pagamentoColor }}">
                                {{ $pagamentoLabel }}
                            </span>
                        </dd>
                        @endif
                        
                        @if($pedido->forma_pagamento)
                        <dt class="col-sm-5">Forma de Pagamento</dt>
                        <dd class="col-sm-7">{{ ucfirst($pedido->forma_pagamento) }}</dd>
                        @endif
                    </dl>
                </div>
            </div>
        </div>

        <!-- Endereço de Entrega -->
        <div class="col-md-4">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white border-bottom-0">
                    <h5 class="mb-0"><i class="bi bi-geo-alt me-2 text-primary"></i>Endereço de Entrega</h5>
                </div>
                <div class="card-body">
                    @if($pedido->endereco_entrega)
                        <p class="mb-1">{{ $pedido->endereco_entrega }}</p>
                        <p class="mb-1">{{ $pedido->cidade }}, {{ $pedido->estado }}</p>
                        <p class="mb-0">CEP: {{ $pedido->cep }}</p>
                    @else
                        <p class="text-muted">Endereço não informado</p>
                    @endif
                </div>
            </div>
        </div>

        <!-- Resumo Financeiro -->
        <div class="col-md-4">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white border-bottom-0">
                    <h5 class="mb-0"><i class="bi bi-cash-stack me-2 text-success"></i>Resumo Financeiro</h5>
                </div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-sm-6">Subtotal</dt>
                        <dd class="col-sm-6 text-end">R$ {{ number_format($pedido->subtotal, 2, ',', '.') }}</dd>
                        
                        @if($pedido->desconto > 0)
                        <dt class="col-sm-6 text-danger">Desconto</dt>
                        <dd class="col-sm-6 text-end text-danger">- R$ {{ number_format($pedido->desconto, 2, ',', '.') }}</dd>
                        @endif
                        
                        <dt class="col-sm-6 fw-bold">Total</dt>
                        <dd class="col-sm-6 text-end fw-bold text-success">
                            R$ {{ number_format($pedido->total, 2, ',', '.') }}
                        </dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>

    <!-- Itens do Pedido -->
    <div class="row g-4 mt-2">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white border-bottom-0">
                    <h5 class="mb-0"><i class="bi bi-box-seam me-2 text-primary"></i>Itens do Pedido</h5>
                </div>
                <div class="card-body">
                    @if($pedido->itens && $pedido->itens->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Produto</th>
                                        <th class="text-center">Quantidade</th>
                                        <th class="text-end">Preço Unit.</th>
                                        <th class="text-end">Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($pedido->itens as $item)
                                        <tr>
                                            <td>
                                                <strong>{{ $item->produto->descricao ?? 'Produto #' . $item->produto_id }}</strong>
                                                @if($item->variacao)
                                                    <br>
                                                    <small class="text-muted">{{ $item->variacao }}</small>
                                                @endif
                                            </td>
                                            <td class="text-center">{{ $item->quantidade }}</td>
                                            <td class="text-end">R$ {{ number_format($item->preco_unitario, 2, ',', '.') }}</td>
                                            <td class="text-end">R$ {{ number_format($item->subtotal, 2, ',', '.') }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="3" class="text-end fw-bold">Total</td>
                                        <td class="text-end fw-bold text-success">
                                            R$ {{ number_format($pedido->total, 2, ',', '.') }}
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    @else
                        <p class="text-muted text-center py-4">
                            <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                            Nenhum item encontrado neste pedido.
                        </p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Ações -->
    <div class="row g-4 mt-2">
        <div class="col-12">
            <div class="d-flex gap-2">
                <a href="{{ route('cliente.pedidos.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left"></i> Voltar
                </a>
                
                @if($pedido->status && $pedido->status->value === 'pendente')
                    <form action="{{ route('cliente.pedidos.cancelar', $pedido->id) }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-danger" onclick="return confirm('Tem certeza que deseja cancelar este pedido?')">
                            <i class="bi bi-x-circle"></i> Cancelar Pedido
                        </button>
                    </form>
                @endif

                @if($pedido->status && $pedido->status->value === 'entregue')
                    <button type="button" class="btn btn-success" disabled>
                        <i class="bi bi-check-circle"></i> Pedido Entregue
                    </button>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection