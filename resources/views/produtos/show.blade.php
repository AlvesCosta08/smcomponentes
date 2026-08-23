@extends('layouts.app')

@section('title', $produto->descricao . ' - SM Componentes')

@section('content')
<div class="container py-4">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
            <li class="breadcrumb-item"><a href="{{ route('produtos.index') }}">Produtos</a></li>
            @if($produto->categoria)
                <li class="breadcrumb-item"><a href="{{ route('produtos.categoria', $produto->categoria) }}">{{ $produto->categoria }}</a></li>
            @endif
            <li class="breadcrumb-item active" aria-current="page">{{ $produto->descricao }}</li>
        </ol>
    </nav>

    <div class="row g-4">
        <!-- Imagem -->
        <div class="col-md-5">
            <div class="product-image-container">
                @if($produto->imagem)
                    @php
                        $filename = basename($produto->imagem);
                    @endphp
                    <img src="{{ asset('storage/produtos/' . $filename) }}" 
                         alt="{{ $produto->descricao }}" 
                         class="img-fluid rounded-3 w-100"
                         style="object-fit: contain; max-height: 400px; background: #f8f9fa; padding: 20px;">
                @else
                    <div class="d-flex align-items-center justify-content-center w-100" style="height: 300px; background: #f8f9fa;">
                        <i class="bi bi-image" style="font-size: 4rem; color: #ccc;"></i>
                    </div>
                @endif
            </div>
        </div>

        <!-- Informações -->
        <div class="col-md-7">
            <h1 class="display-6 fw-bold">{{ $produto->descricao }}</h1>
            
            <p class="text-muted mb-2">
                <i class="bi bi-tag"></i> {{ $produto->categoria ?? 'Sem categoria' }}
                @if($produto->tipo)
                    | <i class="bi bi-box"></i> {{ $produto->tipo }}
                @endif
            </p>
            
            @if($produto->referencia)
                <p class="text-muted small">
                    <i class="bi bi-upc-scan"></i> Referência: {{ $produto->referencia }}
                </p>
            @endif
            
            <!-- STATUS -->
            <div class="mb-3">
                @if($produto->pode_comprar)
                    <span class="badge bg-success fs-6">
                        <i class="bi bi-check-circle"></i> ✓ Disponível
                    </span>
                @else
                    <span class="badge bg-danger fs-6">
                        <i class="bi bi-x-circle"></i> ✗ Indisponível
                    </span>
                @endif
                <span class="badge bg-secondary ms-2">Estoque: {{ $produto->quantidade }} unidades</span>
            </div>
            
            <!-- PREÇO -->
            <div class="mb-4">
                @if($produto->tem_promocao)
                    <h2 class="price text-success">
                        {{ $produto->preco_promocional_formatado }}
                    </h2>
                    <p class="text-muted">
                        <span class="old-price text-decoration-line-through text-danger">
                            {{ $produto->preco_atacado_formatado }}
                        </span>
                        <span class="badge bg-danger ms-2">-{{ $produto->desconto_percentual }}%</span>
                    </p>
                @else
                    <h2 class="price">{{ $produto->preco_atacado_formatado }}</h2>
                @endif
            </div>

            <!-- BOTÃO ADICIONAR AO CARRINHO -->
            @if($produto->pode_comprar)
                <form action="{{ route('carrinho.adicionar') }}" method="POST" class="row g-2" id="form-carrinho">
                    @csrf
                    <input type="hidden" name="produto_id" value="{{ $produto->id }}">
                    <div class="col-auto">
                        <label for="quantidade" class="visually-hidden">Quantidade</label>
                        <input type="number" name="quantidade" id="quantidade" value="1" min="1" max="{{ $produto->quantidade }}" class="form-control" style="width: 80px;">
                    </div>
                    <div class="col-auto">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="bi bi-cart-plus"></i> Adicionar ao Carrinho
                        </button>
                    </div>
                </form>
            @else
                <button class="btn btn-secondary btn-lg" disabled>
                    <i class="bi bi-cart-x"></i> Indisponível
                </button>
            @endif
        </div>
    </div>

    <!-- Descrição -->
    @if($produto->descricao)
        <hr class="my-5">
        <h3 class="mb-3">Descrição</h3>
        <p class="text-muted">{{ $produto->descricao }}</p>
    @endif

    <!-- Produtos Relacionados -->
    @if(isset($relacionados) && $relacionados->count() > 0)
        <hr class="my-5">
        <h3 class="mb-4">Produtos Relacionados</h3>
        <div class="row g-3">
            @foreach($relacionados as $relacionado)
                <div class="col-6 col-md-3">
                    <div class="card h-100 shadow-sm hover-card">
                        @if($relacionado->imagem)
                            @php
                                $filename = basename($relacionado->imagem);
                            @endphp
                            <img src="{{ asset('storage/produtos/' . $filename) }}" 
                                 class="card-img-top" 
                                 alt="{{ $relacionado->descricao }}"
                                 style="height: 150px; object-fit: contain; padding: 10px; background: #f8f9fa;">
                        @else
                            <div class="d-flex align-items-center justify-content-center" style="height: 150px; background: #f8f9fa;">
                                <i class="bi bi-image" style="font-size: 2rem; color: #ccc;"></i>
                            </div>
                        @endif
                        <div class="card-body">
                            <h6 class="card-title text-truncate" title="{{ $relacionado->descricao }}">
                                {{ $relacionado->descricao }}
                            </h6>
                            <p class="card-text text-primary fw-bold">
                                {{ $relacionado->preco_atacado_formatado }}
                            </p>
                            <a href="{{ route('produtos.show', $relacionado->slug) }}" class="btn btn-sm btn-outline-primary w-100">
                                Ver Produto
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>

<style>
    .price {
        font-weight: 700;
        font-size: 2rem;
        color: #0d6efd;
    }
    .old-price {
        font-size: 1.1rem;
    }
    .product-image-container {
        background: #f8f9fa;
        border-radius: 8px;
        min-height: 300px;
        display: flex;
        align-items: center;
        justify-content: center;
        border: 1px solid #dee2e6;
    }
    .hover-card {
        transition: all 0.3s ease;
        border: 1px solid #eee;
    }
    .hover-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 30px rgba(0,0,0,0.1) !important;
    }
</style>
@endsection