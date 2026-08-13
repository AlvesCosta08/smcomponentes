@extends('layouts.app')

@section('title', 'Meu Dashboard')

@section('content')
<div class="container py-4">
    <div class="row">
        <div class="col-12">
            <h1 class="display-5 fw-bold">
                <i class="fas fa-user-circle text-primary me-2"></i>
                👋 Meu Dashboard
            </h1>
            <p class="text-muted">
                <i class="fas fa-user me-1 text-primary"></i>
                Bem-vindo, <strong>{{ Auth::user()->name ?? 'Usuário' }}</strong>!
            </p>
            <hr>
        </div>
    </div>

    <!-- Cards do Cliente - COM FONT AWESOME -->
    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="card text-white bg-primary shadow-lg border-0">
                <div class="card-body">
                    <h6 class="card-title text-uppercase fw-bold opacity-75">
                        <i class="fas fa-box me-2"></i> Meus Pedidos
                    </h6>
                    <h2 class="display-5 fw-bold">{{ $totalPedidos ?? 0 }}</h2>
                    <small class="opacity-75">
                        @if(isset($pedidosPendentes) && $pedidosPendentes > 0)
                            <i class="fas fa-clock me-1"></i> {{ $pedidosPendentes }} pendentes
                        @else
                            <i class="fas fa-check-circle me-1"></i> Nenhum pedido pendente
                        @endif
                    </small>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-white bg-success shadow-lg border-0">
                <div class="card-body">
                    <h6 class="card-title text-uppercase fw-bold opacity-75">
                        <i class="fas fa-money-bill-wave me-2"></i> Total Gasto
                    </h6>
                    <h2 class="display-6 fw-bold">R$ {{ number_format($totalGasto ?? 0, 2, ',', '.') }}</h2>
                    <small class="opacity-75">
                        <i class="fas fa-calendar-alt me-1"></i> Em todos os pedidos
                    </small>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-dark bg-warning shadow-lg border-0">
                <div class="card-body">
                    <h6 class="card-title text-uppercase fw-bold opacity-75">
                        <i class="fas fa-spinner me-2"></i> Status
                    </h6>
                    <h2 class="display-5 fw-bold">{{ $pedidosPendentes ?? 0 }}</h2>
                    <small class="opacity-75">
                        <i class="fas fa-hourglass-half me-1"></i> Pedidos em andamento
                    </small>
                </div>
            </div>
        </div>
    </div>

    <!-- Últimos Pedidos -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white border-bottom-0 py-3">
                    <h5 class="mb-0">
                        <i class="fas fa-clock me-2 text-primary"></i>
                        Meus Últimos Pedidos
                        @if(isset($ultimosPedidos) && $ultimosPedidos->count() > 0)
                            <span class="badge bg-primary ms-2">{{ $ultimosPedidos->count() }}</span>
                        @endif
                    </h5>
                </div>
                <div class="card-body">
                    @if(isset($ultimosPedidos) && $ultimosPedidos->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th><i class="fas fa-hashtag me-1"></i> Pedido</th>
                                        <th><i class="fas fa-calendar-alt me-1"></i> Data</th>
                                        <th><i class="fas fa-dollar-sign me-1"></i> Total</th>
                                        <th><i class="fas fa-info-circle me-1"></i> Status</th>
                                        <th><i class="fas fa-cog me-1"></i> Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($ultimosPedidos as $pedido)
                                        <tr>
                                            <td>
                                                <strong>#{{ $pedido->numero_pedido ?? $pedido->id }}</strong>
                                            </td>
                                            <td>
                                                <i class="far fa-calendar-alt text-muted me-1"></i>
                                                {{ $pedido->created_at->format('d/m/Y H:i') }}
                                            </td>
                                            <td>
                                                <span class="fw-bold text-success">
                                                    <i class="fas fa-credit-card me-1"></i>
                                                    R$ {{ number_format($pedido->total, 2, ',', '.') }}
                                                </span>
                                            </td>
                                            <td>
                                                @php
                                                    $statusColors = [
                                                        'pendente' => 'warning',
                                                        'pago' => 'info',
                                                        'processando' => 'primary',
                                                        'enviado' => 'info',
                                                        'entregue' => 'success',
                                                        'cancelado' => 'danger'
                                                    ];
                                                    $statusIcons = [
                                                        'pendente' => 'fa-clock',
                                                        'pago' => 'fa-credit-card',
                                                        'processando' => 'fa-spinner fa-spin',
                                                        'enviado' => 'fa-truck',
                                                        'entregue' => 'fa-check-circle',
                                                        'cancelado' => 'fa-times-circle'
                                                    ];
                                                    $statusLabels = [
                                                        'pendente' => 'Pendente',
                                                        'pago' => 'Pago',
                                                        'processando' => 'Processando',
                                                        'enviado' => 'Enviado',
                                                        'entregue' => 'Entregue',
                                                        'cancelado' => 'Cancelado'
                                                    ];
                                                @endphp
                                                <span class="badge bg-{{ $statusColors[$pedido->status] ?? 'secondary' }} fs-6">
                                                    <i class="fas {{ $statusIcons[$pedido->status] ?? 'fa-circle' }} me-1"></i>
                                                    {{ $statusLabels[$pedido->status] ?? ucfirst($pedido->status) }}
                                                </span>
                                            </td>
                                            <td>
                                                <a href="{{ route('checkout.detalhes', $pedido->id) }}" 
                                                   class="btn btn-sm btn-outline-primary">
                                                    <i class="fas fa-eye"></i> Ver
                                                </a>
                                                @if($pedido->status === 'pendente')
                                                    <button class="btn btn-sm btn-outline-danger" 
                                                            onclick="if(confirm('Deseja cancelar este pedido?')) document.getElementById('cancelar-{{ $pedido->id }}').submit();">
                                                        <i class="fas fa-times"></i>
                                                    </button>
                                                    <form id="cancelar-{{ $pedido->id }}" 
                                                          action="{{ route('checkout.cancelar', $pedido->id) }}" 
                                                          method="POST" class="d-none">
                                                        @csrf
                                                    </form>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-3 text-center">
                            <a href="{{ route('checkout.pedidos') }}" class="btn btn-outline-primary">
                                <i class="fas fa-list me-2"></i> Ver todos os meus pedidos
                            </a>
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-inbox fs-1 d-block mb-3 text-muted"></i>
                            <p class="text-muted fs-5">Você ainda não fez nenhum pedido.</p>
                            <p class="text-muted">Comece a explorar nossos produtos!</p>
                            <a href="{{ route('produtos.index') }}" class="btn btn-primary btn-lg mt-2">
                                <i class="fas fa-shopping-cart me-2"></i> Começar a Comprar
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Ações Rápidas do Cliente -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white border-bottom-0 py-3">
                    <h5 class="mb-0">
                        <i class="fas fa-bolt me-2 text-warning"></i>
                        Ações Rápidas
                    </h5>
                </div>
                <div class="card-body">
                    <div class="d-flex flex-wrap gap-2">
                        <a href="{{ route('produtos.index') }}" class="btn btn-primary">
                            <i class="fas fa-shopping-bag me-2"></i> Continuar Comprando
                        </a>
                        <a href="{{ route('checkout.pedidos') }}" class="btn btn-info text-white">
                            <i class="fas fa-box me-2"></i> Meus Pedidos
                        </a>
                        <a href="{{ route('profile.edit') }}" class="btn btn-secondary">
                            <i class="fas fa-user-cog me-2"></i> Editar Perfil
                        </a>
                        <a href="{{ route('home') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-home me-2"></i> Voltar ao Início
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    /* Cards com hover effect */
    .card {
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        border-radius: 16px;
    }
    .card:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 30px rgba(0,0,0,0.15) !important;
    }
    
    /* Badge status com ícones */
    .badge {
        padding: 8px 16px;
        border-radius: 50px;
        font-weight: 500;
    }
    
    /* Tabela com hover */
    .table-hover tbody tr {
        transition: background-color 0.2s ease;
    }
    .table-hover tbody tr:hover {
        background-color: #f8f9fa;
    }
    
    /* Botões com ícones */
    .btn {
        border-radius: 50px;
        padding: 10px 24px;
        font-weight: 500;
        transition: all 0.3s ease;
    }
    .btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(0,0,0,0.1);
    }
    .btn-sm {
        padding: 6px 14px;
        border-radius: 50px;
    }
    
    /* Responsivo */
    @media (max-width: 768px) {
        .display-5 {
            font-size: 1.8rem;
        }
        .display-6 {
            font-size: 1.4rem;
        }
        .card-body {
            padding: 1.2rem;
        }
        .d-flex.flex-wrap.gap-2 .btn {
            width: 100%;
            justify-content: center;
        }
    }
</style>
@endsection
