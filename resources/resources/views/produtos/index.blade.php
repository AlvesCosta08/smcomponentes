@extends('layouts.app')

@section('title', 'Produtos - SM Componentes')

@section('content')
<div class="container py-4">
    <!-- Cabeçalho -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
        <h2 class="fw-bold mb-0">
            <i class="bi bi-grid-3x3-gap-fill text-primary"></i> 
            Nossos Produtos
            <span class="badge bg-secondary ms-2">{{ $produtos->total() }}</span>
        </h2>
        
        <div class="d-flex gap-2 mt-2 mt-sm-0">
            <select class="form-select form-select-sm" style="width: auto;" onchange="location.href=this.value">
                <option value="{{ request()->fullUrlWithQuery(['order' => 'created_at', 'dir' => 'desc']) }}" {{ request('order') == 'created_at' ? 'selected' : '' }}>
                    Mais recentes
                </option>
                <option value="{{ request()->fullUrlWithQuery(['order' => 'valor_unitario', 'dir' => 'asc']) }}" {{ request('order') == 'valor_unitario' && request('dir') == 'asc' ? 'selected' : '' }}>
                    Menor preço
                </option>
                <option value="{{ request()->fullUrlWithQuery(['order' => 'valor_unitario', 'dir' => 'desc']) }}" {{ request('order') == 'valor_unitario' && request('dir') == 'desc' ? 'selected' : '' }}>
                    Maior preço
                </option>
                <option value="{{ request()->fullUrlWithQuery(['order' => 'descricao', 'dir' => 'asc']) }}" {{ request('order') == 'descricao' ? 'selected' : '' }}>
                    Nome A-Z
                </option>
            </select>
        </div>
    </div>

    <!-- Filtros de Disponibilidade -->
    @if(isset($totais))
    <div class="mb-4">
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('produtos.index') }}" class="btn btn-outline-secondary btn-sm {{ !request('status') ? 'active' : '' }}">
                <i class="bi bi-grid"></i> Todos ({{ $totais['total'] ?? 0 }})
            </a>
            <a href="{{ route('produtos.filtro', 'disponiveis') }}" class="btn btn-outline-success btn-sm {{ request('status') == 'disponiveis' ? 'active' : '' }}">
                <i class="bi bi-check-circle"></i> Disponíveis ({{ $totais['disponiveis'] ?? 0 }})
            </a>
            <a href="{{ route('produtos.filtro', 'estoque_baixo') }}" class="btn btn-outline-warning btn-sm {{ request('status') == 'estoque_baixo' ? 'active' : '' }}">
                <i class="bi bi-exclamation-triangle"></i> Estoque Baixo ({{ $totais['estoque_baixo'] ?? 0 }})
            </a>
            <a href="{{ route('produtos.filtro', 'indisponiveis') }}" class="btn btn-outline-danger btn-sm {{ request('status') == 'indisponiveis' ? 'active' : '' }}">
                <i class="bi bi-x-circle"></i> Indisponíveis ({{ $totais['indisponiveis'] ?? 0 }})
            </a>
        </div>
    </div>
    @endif

    <!-- Produtos -->
    @if($produtos->isEmpty())
        <div class="alert alert-info text-center py-5">
            <i class="bi bi-search display-4 d-block mb-3"></i>
            <h4>Nenhum produto encontrado</h4>
            <p class="mb-0">Tente ajustar os filtros ou buscar por outro termo.</p>
        </div>
    @else
        <div class="row g-4">
            @foreach($produtos as $produto)
                <div class="col-xl-3 col-lg-4 col-md-6 col-6">
                    <div class="card product-card h-100">
                        <div class="position-relative">
                            @if($produto->imagem)
                                <img src="{{ asset('storage/' . $produto->imagem) }}" class="card-img-top" alt="{{ $produto->descricao }}" style="height: 200px; object-fit: cover;">
                            @else
                                <div class="card-img-top d-flex align-items-center justify-content-center bg-light" style="height: 200px;">
                                    <i class="bi bi-plug" style="font-size: 3.5rem; color: #dfe6e9;"></i>
                                </div>
                            @endif
                            
                            <!-- Badge de Disponibilidade -->
                            @if($produto->disponibilidade == 'DISPONIVEL')
                                <span class="badge bg-success position-absolute top-0 end-0 m-2 px-3 py-1 rounded-pill">
                                    <i class="bi bi-check-circle"></i> Disponível
                                </span>
                            @elseif($produto->disponibilidade == 'EST.BAIXO')
                                <span class="badge bg-warning text-dark position-absolute top-0 end-0 m-2 px-3 py-1 rounded-pill">
                                    <i class="bi bi-exclamation-triangle"></i> Estoque Baixo
                                </span>
                            @else
                                <span class="badge bg-danger position-absolute top-0 end-0 m-2 px-3 py-1 rounded-pill">
                                    <i class="bi bi-x-circle"></i> Indisponível
                                </span>
                            @endif
                        </div>
                        <div class="card-body d-flex flex-column">
                            <h6 class="card-title text-truncate" title="{{ $produto->descricao }}">
                                {{ Str::limit($produto->descricao, 40) }}
                            </h6>
                            <div class="mt-auto">
                                <span class="categoria-badge">
                                    <i class="bi bi-tag"></i> {{ Str::limit($produto->categoria, 20) }}
                                </span>
                                <p class="card-text mt-2 mb-0">
                                    <span class="price">R$ {{ number_format($produto->valor_unitario ?? $produto->valor_atacado, 2, ',', '.') }}</span>
                                    @if($produto->preco_promocional && $produto->preco_promocional < $produto->valor_atacado)
                                        <span class="old-price">R$ {{ number_format($produto->valor_atacado, 2, ',', '.') }}</span>
                                    @endif
                                </p>
                                <a href="{{ route('produtos.show', $produto->slug) }}" class="btn btn-outline-primary w-100 mt-2 rounded-pill">
                                    <i class="bi bi-eye"></i> Detalhes
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Paginação Moderna -->
        <div class="mt-5 pt-3 border-top">
            <div class="d-flex flex-wrap justify-content-between align-items-center">
                <div class="text-muted small mb-2 mb-md-0">
                    <i class="bi bi-info-circle"></i> 
                    Mostrando <strong>{{ $produtos->firstItem() }}</strong> a <strong>{{ $produtos->lastItem() }}</strong> 
                    de <strong>{{ $produtos->total() }}</strong> produtos
                </div>
                <div>
                    {{ $produtos->links('pagination::bootstrap-5') }}
                </div>
            </div>
        </div>
    @endif
</div>
@endsection

@push('styles')
<style>
    .product-card {
        transition: transform 0.2s, box-shadow 0.2s;
        border-radius: 12px;
        overflow: hidden;
        border: 1px solid #e9ecef;
    }
    .product-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    }
    .product-card .card-img-top {
        border-bottom: 1px solid #e9ecef;
    }
    .categoria-badge {
        font-size: 0.75rem;
        color: #6c757d;
        background: #f8f9fa;
        padding: 2px 10px;
        border-radius: 20px;
        border: 1px solid #e9ecef;
    }
    .price {
        font-size: 1.1rem;
        font-weight: 700;
        color: #0d6efd;
    }
    .old-price {
        font-size: 0.8rem;
        color: #dc3545;
        text-decoration: line-through;
        margin-left: 8px;
    }
    .btn-outline-primary {
        border-width: 2px;
    }
    /* Filtros ativos */
    .btn-outline-success.active {
        background: #198754;
        color: #fff;
        border-color: #198754;
    }
    .btn-outline-warning.active {
        background: #ffc107;
        color: #000;
        border-color: #ffc107;
    }
    .btn-outline-danger.active {
        background: #dc3545;
        color: #fff;
        border-color: #dc3545;
    }
    .btn-outline-secondary.active {
        background: #6c757d;
        color: #fff;
        border-color: #6c757d;
    }
</style>
@endpush