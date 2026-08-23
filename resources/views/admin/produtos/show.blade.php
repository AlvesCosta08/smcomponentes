@extends('layouts.app')

@section('title', $produto->descricao . ' - SM Componentes')

@section('content')
<div class="container py-4">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
            <li class="breadcrumb-item"><a href="{{ route('produtos.index') }}">Produtos</a></li>
            <li class="breadcrumb-item"><a href="{{ route('produtos.categoria', $produto->categoria) }}">{{ $produto->categoria }}</a></li>
            <li class="breadcrumb-item active" aria-current="page">{{ $produto->descricao }}</li>
        </ol>
    </nav>

    <div class="row g-4">
        <!-- Imagem -->
        <div class="col-md-5">
            <div class="product-image-container">
                <img src="{{ $produto->imagem_url }}" 
                     alt="{{ $produto->descricao }}" 
                     class="img-fluid rounded-3 w-100"
                     style="object-fit: contain; max-height: 400px; background: #f8f9fa; padding: 20px;">
            </div>
        </div>

        <!-- Informações -->
        <div class="col-md-7">
            <h1 class="display-6 fw-bold">{{ $produto->descricao }}</h1>
            
            <p class="text-muted mb-2">
                <i class="bi bi-tag"></i> {{ $produto->categoria }}
                @if($produto->tipo)
                    | <i class="bi bi-box"></i> {{ $produto->tipo }}
                @endif
            </p>
            
            <!-- Referência -->
            @if($produto->referencia)
                <p class="text-muted small">
                    <i class="bi bi-upc-scan"></i> Referência: {{ $produto->referencia }}
                </p>
            @endif
            
            <!-- Disponibilidade -->
            <div class="mb-3">
                <span class="badge bg-{{ $produto->disponivel ? 'success' : 'danger' }} fs-6">
                    {{ $produto->disponivel ? '✓ Disponível' : '✗ Indisponível' }}
                </span>
                <span class="badge bg-secondary ms-2">Estoque: {{ $produto->quantidade }} unidades</span>
                <span class="badge bg-{{ str_contains(strtolower($produto->status_label), 'disponível') ? 'success' : 'warning' }} ms-2">
                    {{ $produto->status_label }}
                </span>
            </div>
            
            <!-- Preço -->
            <div class="mb-4">
                @if($produto->tem_promocao)
                    <h2 class="price text-success">{{ $produto->preco_promocional_formatado }}</h2>
                    <p class="text-muted">
                        <span class="old-price text-decoration-line-through text-danger">
                            {{ $produto->preco_formatado }}
                        </span>
                        <span class="badge bg-danger ms-2">Promoção</span>
                    </p>
                @else
                    <h2 class="price">{{ $produto->preco_formatado }}</h2>
                @endif
            </div>

            <!-- Botão Adicionar ao Carrinho -->
            @if($produto->disponivel)
                <form action="{{ route('carrinho.adicionar') }}" method="POST" class="row g-2">
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

            <!-- Wishlist -->
            @auth
                <button class="btn btn-outline-danger mt-3" onclick="toggleWishlist({{ $produto->id }})">
                    <i class="bi bi-heart"></i> Favoritar
                </button>
            @endauth
        </div>
    </div>

    <!-- Descrição -->
    @if($produto->descricao)
        <hr class="my-5">
        <h3 class="mb-3">Descrição</h3>
        <p class="text-muted">{{ $produto->descricao }}</p>
    @endif

    <!-- Produtos Relacionados -->
    @if(!empty($relacionados) && count($relacionados) > 0)
        <hr class="my-5">
        <h3 class="mb-4">Produtos Relacionados</h3>
        <div class="row g-3">
            @foreach($relacionados as $relacionado)
                <div class="col-6 col-md-3">
                    <div class="card h-100 shadow-sm hover-card">
                        <img src="{{ $relacionado->imagem_url }}" 
                             class="card-img-top" 
                             alt="{{ $relacionado->descricao }}"
                             style="height: 150px; object-fit: contain; padding: 10px; background: #f8f9fa;">
                        <div class="card-body">
                            <h6 class="card-title text-truncate" title="{{ $relacionado->descricao }}">
                                {{ $relacionado->descricao }}
                            </h6>
                            <p class="card-text text-primary fw-bold">{{ $relacionado->preco_formatado }}</p>
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

@push('scripts')
<script>
function toggleWishlist(produtoId) {
    // Implementar lógica da wishlist
    alert('Funcionalidade em desenvolvimento!');
}
</script>
@endpush
@endsection