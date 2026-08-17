@extends('layouts.app')

@section('title', 'Meu Dashboard - SM Componentes')

@section('content')
<div class="container-fluid py-4">
    <!-- HEADER -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card text-white" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 20px;">
                <div class="card-body py-4">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h1 class="display-5 fw-bold mb-2">
                                <i class="fas fa-user-circle me-2"></i>
                                Olá, {{ Auth::user()->name }}! 👋
                            </h1>
                            <p class="text-white-50 mb-0 fs-5">
                                <i class="fas fa-calendar-alt me-2"></i>
                                {{ now()->format('d/m/Y') }}
                            </p>
                            <p class="text-white-50 mt-2">
                                <i class="fas fa-envelope me-2"></i>
                                {{ Auth::user()->email }}
                            </p>
                        </div>
                        <div class="col-md-4 text-md-end">
                            <span class="badge bg-white text-primary px-4 py-2 fs-6">
                                <i class="fas fa-crown me-2"></i>
                                {{ Auth::user()->roles->first()->name ?? 'Cliente' }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- CARDS ESTATÍSTICAS -->
    <div class="row g-4 mb-4">
        <!-- Total Pedidos -->
        <div class="col-xl-3 col-lg-6 col-md-6">
            <a href="{{ route('cliente.pedidos') }}" class="text-decoration-none">
                <div class="card text-white bg-primary">
                    <div class="card-body">
                        <h6 class="text-uppercase fw-bold opacity-75">
                            <i class="fas fa-box me-2"></i> Total Pedidos
                        </h6>
                        <h2 class="display-4 fw-bold">{{ $totalPedidos ?? 0 }}</h2>
                        <small class="opacity-75">
                            <i class="fas fa-clock me-1"></i>
                            {{ $pedidosPendentes ?? 0 }} em andamento
                        </small>
                    </div>
                </div>
            </a>
        </div>

        <!-- Total Gasto -->
        <div class="col-xl-3 col-lg-6 col-md-6">
            <div class="card text-white bg-success">
                <div class="card-body">
                    <h6 class="text-uppercase fw-bold opacity-75">
                        <i class="fas fa-coins me-2"></i> Total Gasto
                    </h6>
                    <h2 class="display-4 fw-bold">
                        R$ {{ number_format($totalGasto ?? 0, 2, ',', '.') }}
                    </h2>
                    <small class="opacity-75">
                        <i class="fas fa-calendar-alt me-1"></i> Em todos os pedidos
                    </small>
                </div>
            </div>
        </div>

        <!-- Pedidos Pendentes -->
        <div class="col-xl-3 col-lg-6 col-md-6">
            <a href="{{ route('cliente.pedidos') }}" class="text-decoration-none">
                <div class="card text-white bg-warning">
                    <div class="card-body">
                        <h6 class="text-uppercase fw-bold opacity-75">
                            <i class="fas fa-spinner me-2"></i> Pendentes
                        </h6>
                        <h2 class="display-4 fw-bold">{{ $pedidosPendentes ?? 0 }}</h2>
                        <small class="opacity-75">
                            <i class="fas fa-clock me-1"></i> Aguardando
                        </small>
                    </div>
                </div>
            </a>
        </div>

        <!-- Wishlist -->
        <div class="col-xl-3 col-lg-6 col-md-6">
            <a href="{{ route('wishlist.index') }}" class="text-decoration-none">
                <div class="card text-white bg-info">
                    <div class="card-body">
                        <h6 class="text-uppercase fw-bold opacity-75">
                            <i class="fas fa-heart me-2"></i> Wishlist
                        </h6>
                        <h2 class="display-4 fw-bold">{{ $wishlistCount ?? 0 }}</h2>
                        <small class="opacity-75">
                            <i class="fas fa-gem me-1"></i> Produtos favoritos
                        </small>
                    </div>
                </div>
            </a>
        </div>
    </div>

    <!-- ÚLTIMOS PEDIDOS -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-list-ul text-primary me-2"></i>
                        Meus Últimos Pedidos
                    </h5>
                    <a href="{{ route('cliente.pedidos') }}" class="btn btn-outline-primary btn-sm">
                        <i class="fas fa-eye me-1"></i> Ver Todos
                    </a>
                </div>
                <div class="card-body">
                    @if(isset($ultimosPedidos) && $ultimosPedidos->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>Pedido</th>
                                        <th>Data</th>
                                        <th>Total</th>
                                        <th>Status</th>
                                        <th class="text-center">Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($ultimosPedidos as $pedido)
                                        <tr>
                                            <td>
                                                <strong>#{{ $pedido->numero_pedido ?? $pedido->id }}</strong>
                                            </td>
                                            <td>{{ $pedido->created_at->format('d/m/Y H:i') }}</td>
                                            <td>
                                                <span class="fw-bold text-success">
                                                    R$ {{ number_format($pedido->total, 2, ',', '.') }}
                                                </span>
                                            </td>
                                            <td>
                                                @php
                                                    $cores = [
                                                        'pendente' => 'warning',
                                                        'pago' => 'info',
                                                        'processando' => 'primary',
                                                        'enviado' => 'info',
                                                        'entregue' => 'success',
                                                        'cancelado' => 'danger'
                                                    ];
                                                    $labels = [
                                                        'pendente' => 'Pendente',
                                                        'pago' => 'Pago',
                                                        'processando' => 'Processando',
                                                        'enviado' => 'Enviado',
                                                        'entregue' => 'Entregue',
                                                        'cancelado' => 'Cancelado'
                                                    ];
                                                @endphp
                                                <span class="badge bg-{{ $cores[$pedido->status] ?? 'secondary' }}">
                                                    <i class="fas fa-circle me-1"></i>
                                                    {{ $labels[$pedido->status] ?? ucfirst($pedido->status) }}
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <a href="{{ route('checkout.detalhes', $pedido) }}" 
                                                   class="btn btn-outline-primary btn-sm"
                                                   title="Ver detalhes">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                @if($pedido->status === 'pendente')
                                                    <button type="button" 
                                                            class="btn btn-outline-danger btn-sm"
                                                            onclick="if(confirm('Cancelar este pedido?')) document.getElementById('cancelar-{{ $pedido->id }}').submit();"
                                                            title="Cancelar pedido">
                                                        <i class="fas fa-times"></i>
                                                    </button>
                                                    <form id="cancelar-{{ $pedido->id }}" 
                                                          action="{{ route('checkout.cancelar', $pedido) }}" 
                                                          method="POST" style="display: none;">
                                                        @csrf
                                                        @method('POST')
                                                    </form>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-inbox fs-1 d-block mb-3 text-muted"></i>
                            <p class="text-muted">Você ainda não fez nenhum pedido.</p>
                            <a href="{{ route('produtos.index') }}" class="btn btn-primary">
                                <i class="fas fa-shopping-cart me-2"></i> Começar a Comprar
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- AÇÕES RÁPIDAS -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h5 class="mb-3">
                        <i class="fas fa-bolt text-warning me-2"></i>
                        Ações Rápidas
                    </h5>
                    <div class="d-flex flex-wrap gap-2">
                        <a href="{{ route('produtos.index') }}" class="btn btn-primary">
                            <i class="fas fa-shopping-bag me-2"></i> Produtos
                        </a>
                        <a href="{{ route('carrinho.index') }}" class="btn btn-success">
                            <i class="fas fa-cart-plus me-2"></i> Carrinho
                            @if(session()->has('carrinho') && count(session()->get('carrinho')) > 0)
                                <span class="badge bg-white text-dark ms-1">
                                    {{ count(session()->get('carrinho')) }}
                                </span>
                            @endif
                        </a>
                        <a href="{{ route('wishlist.index') }}" class="btn btn-warning text-dark">
                            <i class="fas fa-heart me-2"></i> Wishlist
                        </a>
                        <a href="{{ route('profile.edit') }}" class="btn btn-secondary">
                            <i class="fas fa-user-cog me-2"></i> Perfil
                        </a>
                        <a href="#" class="btn btn-danger"
                           onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                            <i class="fas fa-sign-out-alt me-2"></i> Sair
                        </a>
                        <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                            @csrf
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- FOOTER -->
    <div class="row mt-4">
        <div class="col-12 text-center text-muted">
            <small>
                <i class="fas fa-shield-alt me-1"></i>
                Sua conta está segura
                <span class="mx-2">•</span>
                <i class="fas fa-clock me-1"></i>
                Último acesso: {{ Auth::user()->ultimo_acesso ? \Carbon\Carbon::parse(Auth::user()->ultimo_acesso)->format('d/m/Y H:i') : 'Primeiro acesso' }}
                <span class="mx-2">•</span>
                <a href="{{ route('home') }}" class="text-muted text-decoration-none">
                    <i class="fas fa-store me-1"></i> Voltar à loja
                </a>
            </small>
        </div>
    </div>
</div>
@endsection