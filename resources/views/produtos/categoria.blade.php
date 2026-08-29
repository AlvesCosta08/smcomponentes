@extends('layouts.app')

@section('title', 'Produtos por Categoria')

@section('content')
<div class="container py-4">
    <div class="row">
        <div class="col-12">
            <h1 class="h3 mb-4">Categoria: {{ $categoria->nome }}</h1>
            
            <div class="row g-4">
                @forelse($produtos as $produto)
                    <div class="col-md-4 col-lg-3">
                        <div class="card h-100 shadow-sm">
                            <img src="{{ $produto->imagem_url ?? asset('images/produto-placeholder.jpg') }}" 
                                 class="card-img-top" 
                                 alt="{{ $produto->descricao }}"
                                 style="height: 200px; object-fit: cover;">
                            <div class="card-body">
                                <h6 class="card-title">{{ $produto->descricao }}</h6>
                                <p class="card-text fw-bold">R$ {{ number_format($produto->valor_atacado, 2, ',', '.') }}</p>
                                <a href="{{ route('produtos.show', $produto->slug) }}" class="btn btn-primary btn-sm w-100">
                                    Ver Detalhes
                                </a>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-12 text-center py-5">
                        <p class="text-muted">Nenhum produto encontrado nesta categoria.</p>
                    </div>
                @endforelse
            </div>
            
            <div class="mt-4">
                {{ $produtos->links() }}
            </div>
        </div>
    </div>
</div>
@endsection