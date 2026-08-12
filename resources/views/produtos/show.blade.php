@extends('layouts.app')

@section('title', $produto->descricao . ' - SM Componentes')

@section('content')
<div class="container py-4">
    <div class="row">
        <div class="col-md-6">
            @if($produto->imagem)
                <img src="{{ asset('storage/' . $produto->imagem) }}" class="img-fluid rounded" alt="{{ $produto->descricao }}">
            @else
                <div class="d-flex align-items-center justify-content-center bg-light rounded" style="height: 400px;">
                    <i class="bi bi-plug" style="font-size: 5rem; color: #ddd;"></i>
                </div>
            @endif
        </div>
        <div class="col-md-6">
            <h1>{{ $produto->descricao }}</h1>
            <p class="text-muted">
                <i class="bi bi-tag"></i> {{ $produto->categoria }}
                @if($produto->tipo)
                    | <i class="bi bi-box"></i> {{ $produto->tipo }}
                @endif
            </p>
            
            <div class="mb-3">
                <span class="badge bg-{{ $produto->esta_disponivel ? 'success' : 'danger' }} fs-6">
                    {{ $produto->esta_disponivel ? '✓ Disponível' : '✗ Indisponível' }}
                </span>
                <span class="badge bg-secondary ms-2">Estoque: {{ $produto->quantidade }} unidades</span>
            </div>
            
            <div class="mb-4">
                <h2 class="price">R$ {{ number_format($produto->preco_atual, 2, ',', '.') }}</h2>
                @if($produto->valor_atacado && $produto->valor_atacado > $produto->preco_atual)
                    <p class="text-muted">
                        <span class="old-price">R$ {{ number_format($produto->valor_atacado, 2, ',', '.') }}</span>
                        <span class="text-success ms-2">
                            Economize {{ number_format((($produto->valor_atacado - $produto->preco_atual) / $produto->valor_atacado) * 100, 0) }}%
                        </span>
                    </p>
                @endif
            </div>
            
            @if($produto->esta_disponivel)
                <form action="{{ route('carrinho.adicionar') }}" method="POST" class="d-flex gap-2">
                    @csrf
                    <input type="hidden" name="produto_id" value="{{ $produto->id }}">
                    <input type="number" name="quantidade" value="1" min="1" max="{{ $produto->quantidade }}" class="form-control" style="width: 80px;">
                    <button type="submit" class="btn btn-primary btn-lg flex-grow-1">
                        <i class="bi bi-cart-plus"></i> Adicionar ao Carrinho
                    </button>
                </form>
            @else
                <button class="btn btn-secondary btn-lg w-100" disabled>
                    <i class="bi bi-x-circle"></i> Produto Indisponível
                </button>
            @endif
            
            <hr>
            
            <h5>Informações do Produto</h5>
            <table class="table table-sm">
                <tr>
                    <th>Referência:</th>
                    <td><strong>{{ $produto->referencia }}</strong></td>
                </tr>
                @if($produto->valor_compra)
                    <tr>
                        <th>Valor de Compra:</th>
                        <td>R$ {{ number_format($produto->valor_compra, 2, ',', '.') }}</td>
                    </tr>
                @endif
                @if($produto->ipi)
                    <tr>
                        <th>IPI:</th>
                        <td>{{ $produto->ipi }}%</td>
                    </tr>
                @endif
                @if($produto->data_compra)
                    <tr>
                        <th>Data de Compra:</th>
                        <td>{{ \Carbon\Carbon::parse($produto->data_compra)->format('d/m/Y') }}</td>
                    </tr>
                @endif
            </table>
        </div>
    </div>
    
    <!-- Produtos Relacionados -->
    @if($relacionados->isNotEmpty())
        <div class="row mt-5">
            <div class="col-12">
                <h3 class="mb-4">🔄 Produtos Relacionados</h3>
            </div>
            @foreach($relacionados as $related)
                <div class="col-md-2 col-4 mb-4">
                    <div class="card product-card">
                        <div class="card-body text-center">
                            <h6 class="card-title text-truncate" title="{{ $related->descricao }}">
                                {{ Str::limit($related->descricao, 20) }}
                            </h6>
                            <p class="price">R$ {{ number_format($related->preco_atual, 2, ',', '.') }}</p>
                            <a href="{{ route('produtos.show', $related->slug) }}" class="btn btn-sm btn-outline-primary w-100">
                                Ver
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection