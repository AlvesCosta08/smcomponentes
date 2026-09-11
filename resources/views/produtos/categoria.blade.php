@extends('layouts.app')

@section('title', 'Produtos por Categoria - ' . $categoria->nome)

@section('content')
<div class="container py-4">
    <div class="row">
        <div class="col-12">
            <!-- Breadcrumb -->
            <nav aria-label="breadcrumb" class="mb-3">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('produtos.index') }}">Produtos</a></li>
                    <li class="breadcrumb-item active" aria-current="page">{{ $categoria->nome }}</li>
                </ol>
            </nav>

            <h1 class="h3 mb-4">
                <i class="bi bi-tag"></i> Categoria: {{ $categoria->nome }}
                <span class="badge bg-secondary ms-2">{{ $produtos->total() }}</span>
            </h1>
            
            <div class="row g-4">
                @forelse($produtos as $produto)
                    <div class="col-md-4 col-lg-3">
                        <div class="card h-100 shadow-sm hover-card">
                            <div class="position-relative">
                                @if($produto->imagem)
                                    @php
                                        $filename = basename($produto->imagem);
                                    @endphp
                                    <img src="{{ asset('storage/produtos/' . $filename) }}" 
                                         class="card-img-top" 
                                         alt="{{ $produto->descricao }}"
                                         style="height: 200px; object-fit: cover; background: #f8f9fa;"
                                         onerror="this.onerror=null; this.src='{{ asset('images/produto-placeholder.jpg') }}';">
                                @else
                                    <div class="card-img-top d-flex align-items-center justify-content-center bg-light" style="height: 200px;">
                                        <i class="bi bi-plug" style="font-size: 3.5rem; color: #dfe6e9;"></i>
                                    </div>
                                @endif

                                @if($produto->tem_promocao)
                                    <span class="badge bg-danger position-absolute top-0 start-0 m-2 px-3 py-1 rounded-pill">
                                        -{{ $produto->desconto_percentual }}%
                                    </span>
                                @endif

                                @if($produto->pode_comprar)
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
                                    {{ $produto->descricao }}
                                </h6>
                                
                                <div class="mt-auto">
                                    @if($produto->tem_promocao)
                                        <span class="text-decoration-line-through text-muted small">
                                            {{ $produto->preco_atacado_formatado }}
                                        </span>
                                        <span class="fw-bold text-danger">
                                            {{ $produto->preco_promocional_formatado }}
                                        </span>
                                    @else
                                        <p class="card-text fw-bold text-primary mb-0">
                                            {{ $produto->preco_atacado_formatado }}
                                        </p>
                                    @endif
                                    
                                    <a href="{{ route('produtos.show', $produto->slug) }}" 
                                       class="btn btn-outline-primary btn-sm w-100 mt-2 rounded-pill">
                                        <i class="bi bi-eye"></i> Ver Detalhes
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-12 text-center py-5">
                        <i class="bi bi-box display-1 text-muted d-block mb-3"></i>
                        <p class="text-muted fs-5">Nenhum produto encontrado nesta categoria.</p>
                        <a href="{{ route('produtos.index') }}" class="btn btn-primary">
                            <i class="bi bi-arrow-left"></i> Voltar para Produtos
                        </a>
                    </div>
                @endforelse
            </div>
            
            @if($produtos->hasPages())
                <div class="mt-4">
                    {{ $produtos->appends(request()->query())->links('pagination::bootstrap-5') }}
                </div>
            @endif
        </div>
    </div>
</div>

@push('styles')
<style>
    .hover-card {
        transition: transform 0.2s, box-shadow 0.2s;
        border-radius: 12px;
        overflow: hidden;
        border: 1px solid #e9ecef;
    }
    .hover-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 30px rgba(0,0,0,0.1) !important;
    }
    .btn-outline-primary {
        border-width: 2px;
    }
</style>
@endpush
@endsection