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
                    <div class="card product-card">
                        <div class="position-relative">
                            @if($produto->imagem)
                                <img src="{{ asset('storage/' . $produto->imagem) }}" class="card-img-top" alt="{{ $produto->descricao }}">
                            @else
                                <div class="card-img-top d-flex align-items-center justify-content-center bg-light">
                                    <i class="bi bi-plug" style="font-size: 3.5rem; color: #dfe6e9;"></i>
                                </div>
                            @endif
                            @if($produto->esta_disponivel)
                                <span class="badge bg-success position-absolute top-0 end-0 m-2 px-3 py-1 rounded-pill">
                                    <i class="bi bi-check-circle"></i> Disponível
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
                                    <span class="price">R$ {{ number_format($produto->preco_atual, 2, ',', '.') }}</span>
                                    @if($produto->valor_atacado > $produto->preco_atual)
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