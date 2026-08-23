@extends('layouts.app')

@section('title', 'Busca de Produtos')

@section('content')
<div class="container py-4">
    <div class="row">
        <div class="col-12">
            <h1 class="h2 mb-3">Busca de Produtos</h1>
            
            <!-- Barra de busca -->
            <div class="mb-4">
                <form action="{{ route('produtos.buscar') }}" method="GET" class="position-relative">
                    @csrf
                    <input type="text" 
                           name="q"
                           class="form-control form-control-lg" 
                           placeholder="Digite para buscar produtos..." 
                           value="{{ $termo ?? '' }}"
                           autocomplete="off">
                    <button type="submit" class="position-absolute top-50 end-0 translate-middle-y btn btn-primary me-2">
                        <i class="bi bi-search"></i>
                    </button>
                </form>
                <small class="text-muted" id="result-count">
                    @if(isset($produtos))
                        {{ $produtos->total() }} produto(s) encontrado(s)
                    @endif
                </small>
            </div>
        </div>
    </div>

    <!-- Resultados -->
    <div id="search-results">
        @if(isset($produtos) && $produtos->isNotEmpty())
            <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-4" id="products-grid">
                @foreach($produtos as $produto)
                    <div class="col product-item">
                        <div class="card h-100 shadow-sm">
                            <div class="position-relative">
                                @if($produto->imagem)
                                    @php
                                        $filename = basename($produto->imagem);
                                    @endphp
                                    <img src="{{ asset('storage/produtos/' . $filename) }}" 
                                         class="card-img-top" 
                                         alt="{{ $produto->descricao }}"
                                         style="height: 200px; object-fit: cover;"
                                         onerror="this.onerror=null; this.src='{{ asset('images/produto-placeholder.jpg') }}';">
                                @else
                                    <div class="card-img-top d-flex align-items-center justify-content-center bg-light" style="height: 200px;">
                                        <i class="bi bi-image" style="font-size: 3rem; color: #ccc;"></i>
                                    </div>
                                @endif
                                
                                @if($produto->tem_promocao)
                                    <span class="badge bg-danger position-absolute top-0 end-0 m-2">
                                        -{{ $produto->desconto_percentual }}%
                                    </span>
                                @endif
                            </div>
                            
                            <div class="card-body d-flex flex-column">
                                <h5 class="card-title">{{ Str::limit($produto->descricao, 50) }}</h5>
                                
                                @if(isset($produto->categoria))
                                    <small class="text-muted">{{ $produto->categoria }}</small>
                                @else
                                    <small class="text-muted">Sem categoria</small>
                                @endif
                                
                                <div class="mt-2">
                                    @if($produto->tem_promocao)
                                        <span class="text-decoration-line-through text-muted me-2">
                                            {{ $produto->preco_atacado_formatado }}
                                        </span>
                                        <span class="text-danger fw-bold">
                                            {{ $produto->preco_promocional_formatado }}
                                        </span>
                                    @else
                                        <span class="fw-bold">
                                            {{ $produto->preco_atacado_formatado }}
                                        </span>
                                    @endif
                                </div>
                                
                                <div class="mt-2">
                                    @if($produto->pode_comprar)
                                        <span class="badge bg-success">Em estoque</span>
                                    @else
                                        <span class="badge bg-danger">Indisponível</span>
                                    @endif
                                </div>
                            </div>
                            
                            <div class="card-footer bg-transparent border-0">
                                <a href="{{ route('produtos.show', $produto->slug) }}" 
                                   class="btn btn-outline-primary w-100">
                                    Ver detalhes
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
            
            <div class="row mt-4">
                <div class="col-12">
                    {{ $produtos->appends(['q' => $termo ?? ''])->links() }}
                </div>
            </div>
        @elseif(isset($produtos) && $produtos->isEmpty())
            <div class="row">
                <div class="col-12 text-center py-5">
                    <i class="bi bi-search display-1 text-muted"></i>
                    <h3 class="mt-3">Nenhum produto encontrado</h3>
                    <p class="text-muted">Tente buscar por outro termo.</p>
                </div>
            </div>
        @else
            <div class="row">
                <div class="col-12 text-center py-5">
                    <i class="bi bi-search display-1 text-muted"></i>
                    <h3 class="mt-3">Digite algo para buscar</h3>
                    <p class="text-muted">Busque por nome, referência ou categoria.</p>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection