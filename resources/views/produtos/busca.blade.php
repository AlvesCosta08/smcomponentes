@extends('layouts.app')

@section('title', 'Busca de Produtos - SM Componentes')

@section('content')
<div class="container py-4">
    <div class="row">
        <div class="col-12">
            <h1 class="h2 mb-3">
                <i class="bi bi-search"></i> Buscar Produtos
            </h1>

            {{-- Busca com Autocomplete --}}
            <div class="mb-4">
                @include('produtos.partials.autocomplete', [
                    'id'          => 'pagina-busca',
                    'termo'       => $termo ?? '',
                    'placeholder' => 'Digite o nome ou referência do produto...',
                    'inputClass'  => 'form-control-lg',
                ])

                @if(isset($produtos))
                    <small class="text-muted mt-2 d-block">
                        <i class="bi bi-info-circle"></i>
                        {{ $produtos->total() }} produto(s) encontrado(s) para
                        "<strong>{{ $termo }}</strong>"
                    </small>
                @endif
            </div>
        </div>
    </div>

    {{-- Resultados --}}
    @if(isset($produtos) && $produtos->isNotEmpty())
        <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-4">
            @foreach($produtos as $produto)
                <div class="col">
                    <div class="card h-100 shadow-sm product-card">
                        <div class="position-relative">
                            @if($produto->imagem)
                                @php $filename = basename($produto->imagem); @endphp
                                <img src="{{ asset('storage/produtos/' . $filename) }}"
                                     class="card-img-top"
                                     alt="{{ $produto->descricao }}"
                                     style="height: 200px; object-fit: cover;"
                                     onerror="this.onerror=null; this.src='{{ asset('images/produto-placeholder.jpg') }}';">
                            @else
                                <div class="card-img-top d-flex align-items-center justify-content-center bg-light" style="height: 200px;">
                                    <i class="bi bi-plug" style="font-size: 3.5rem; color: #dfe6e9;"></i>
                                </div>
                            @endif

                            @if($produto->tem_promocao)
                                <span class="badge bg-danger position-absolute top-0 start-0 m-2">
                                    -{{ $produto->desconto_percentual }}%
                                </span>
                            @endif

                            @if($produto->pode_comprar)
                                <span class="badge bg-success position-absolute top-0 end-0 m-2">
                                    <i class="bi bi-check-circle"></i> Disponível
                                </span>
                            @else
                                <span class="badge bg-danger position-absolute top-0 end-0 m-2">
                                    <i class="bi bi-x-circle"></i> Indisponível
                                </span>
                            @endif
                        </div>
                        <div class="card-body d-flex flex-column">
                            <h6 class="card-title text-truncate" title="{{ $produto->descricao }}">
                                {{ $produto->descricao }}
                            </h6>
                            @if($produto->referencia)
                                <small class="text-muted">Ref: {{ $produto->referencia }}</small>
                            @endif
                            <small class="text-muted">{{ $produto->categoria?->nome ?? 'Sem categoria' }}</small>

                            <div class="mt-2">
                                @if($produto->tem_promocao)
                                    <span class="text-decoration-line-through text-muted me-2">
                                        {{ $produto->preco_atacado_formatado }}
                                    </span>
                                    <span class="text-danger fw-bold">
                                        {{ $produto->preco_promocional_formatado }}
                                    </span>
                                @else
                                    <span class="fw-bold text-primary">
                                        {{ $produto->preco_atacado_formatado }}
                                    </span>
                                @endif
                            </div>

                            <a href="{{ route('produtos.show', $produto->slug) }}"
                               class="btn btn-outline-primary w-100 mt-2 rounded-pill">
                                <i class="bi bi-eye"></i> Ver detalhes
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="row mt-4">
            <div class="col-12">
                {{ $produtos->appends(['q' => $termo ?? ''])->links('pagination::bootstrap-5') }}
            </div>
        </div>
    @elseif(isset($produtos) && $produtos->isEmpty())
        <div class="row">
            <div class="col-12 text-center py-5">
                <i class="bi bi-search display-1 text-muted"></i>
                <h3 class="mt-3">Nenhum produto encontrado</h3>
                <p class="text-muted">Tente buscar por outro termo ou verifique a ortografia.</p>
                <a href="{{ route('produtos.index') }}" class="btn btn-primary">
                    <i class="bi bi-grid"></i> Ver todos os produtos
                </a>
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
</style>
@endpush