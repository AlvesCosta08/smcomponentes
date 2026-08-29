@extends('layouts.app')

@section('title', 'Meus Pedidos - SM Componentes')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold">
            <i class="bi bi-box-seam text-primary"></i> Meus Pedidos
        </h2>
        <a href="{{ route('home') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Voltar
        </a>
    </div>

    @if($pedidos->isEmpty())
        <div class="text-center py-5">
            <i class="bi bi-box-seam" style="font-size: 5rem; color: #ddd;"></i>
            <h3 class="mt-3">Nenhum pedido encontrado</h3>
            <p class="text-muted">Você ainda não realizou nenhuma compra.</p>
            <a href="{{ route('produtos.index') }}" class="btn btn-primary mt-2">
                <i class="bi bi-grid"></i> Ver Produtos
            </a>
        </div>
    @else
        <div class="row g-4">
            @foreach($pedidos as $pedido)
                <div class="col-12">
                    <div class="card shadow-sm hover-card">
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col-md-3">
                                    <h6 class="fw-bold mb-1">Pedido #{{ $pedido->numero_pedido ?? $pedido->id }}</h6>
                                    <small class="text-muted">
                                        <i class="bi bi-calendar3"></i> {{ $pedido->data_criacao_formatada }}
                                    </small>
                                </div>
                                <div class="col-md-3">
                                    <span class="badge bg-{{ $pedido->status_color }} p-2">
                                        <i class="bi {{ $pedido->status_icon }} me-1"></i>
                                        {{ $pedido->status_label }}
                                    </span>
                                    <br>
                                    <small class="text-muted">
                                        Pagamento: 
                                        <span class="badge bg-{{ $pedido->pagamento_color }} mt-1">
                                            {{ $pedido->pagamento_label }}
                                        </span>
                                    </small>
                                </div>
                                <div class="col-md-3 text-md-end">
                                    <span class="fw-bold text-primary h5">
                                        {{ $pedido->total_formatado }}
                                    </span>
                                </div>
                                <div class="col-md-3 text-md-end">
                                    {{-- Botão Detalhes - CORRIGIDO --}}
                                    <a href="{{ route('cliente.pedidos.detalhes', $pedido) }}" class="btn btn-outline-primary btn-sm">
                                        <i class="bi bi-eye"></i> Detalhes
                                    </a>
                                    
                                    {{-- Botão Cancelar - CORRIGIDO: chamando o método com parênteses --}}
                                    @if($pedido->podeCancelar())
                                        <form action="{{ route('cliente.pedidos.cancelar', $pedido) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-outline-danger btn-sm" 
                                                    onclick="return confirm('Tem certeza que deseja cancelar este pedido?')">
                                                <i class="bi bi-x-circle"></i> Cancelar
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-4">
            {{ $pedidos->links() }}
        </div>
    @endif
</div>

<style>
    .hover-card {
        transition: all 0.3s ease;
        border: 1px solid #eee;
    }
    .hover-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 30px rgba(0,0,0,0.08) !important;
    }
    .badge {
        font-size: 0.85rem;
    }
</style>
@endsection