@extends('layouts.app')

@section('title', 'Loja de Componentes Eletrônicos')

@section('content')
<div class="container py-4">
    {{-- VANTAGENS --}}
    <div class="row text-center my-4 g-3">
        <div class="col-6 col-md-3">
            <div class="p-3 border rounded-3 h-100 shadow-sm">
                <i class="bi bi-truck fs-1 text-primary"></i>
                <h6 class="mt-2">POSTAGEM RÁPIDA</h6>
                <small class="text-muted">Enviamos em até 24h</small>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="p-3 border rounded-3 h-100 shadow-sm">
                <i class="bi bi-credit-card fs-1 text-success"></i>
                <h6 class="mt-2">PARCELAMENTO</h6>
                <small class="text-muted">Em até 6x sem juros</small>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="p-3 border rounded-3 h-100 shadow-sm">
                <i class="bi bi-whatsapp fs-1 text-success"></i>
                <h6 class="mt-2">ATENDIMENTO</h6>
                <small class="text-muted">Via WhatsApp</small>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="p-3 border rounded-3 h-100 shadow-sm">
                <i class="bi bi-shield-lock fs-1 text-danger"></i>
                <h6 class="mt-2">SITE 100% SEGURO</h6>
                <small class="text-muted">Compre com segurança</small>
            </div>
        </div>
    </div>

    {{-- CARROSSEL --}}
    <div class="row mb-4">
        <div class="col-12">
            <div id="bannerCarousel" class="carousel slide" data-bs-ride="carousel">
                <div class="carousel-indicators">
                    <button type="button" data-bs-target="#bannerCarousel" data-bs-slide-to="0" class="active"></button>
                    <button type="button" data-bs-target="#bannerCarousel" data-bs-slide-to="1"></button>
                    <button type="button" data-bs-target="#bannerCarousel" data-bs-slide-to="2"></button>
                </div>
                <div class="carousel-inner rounded-4 shadow">
                    <div class="carousel-item active">
                        <div class="banner-slide" style="background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 50%, #084298 100%); padding: 3rem 2rem; min-height: 200px;">
                            <div class="row align-items-center">
                                <div class="col-md-8 text-white">
                                    <h1 class="display-5 fw-bold">SM Componentes</h1>
                                    <p class="lead mb-0">A melhor loja de componentes eletrônicos da internet!</p>
                                </div>
                                <div class="col-md-4 text-md-end mt-3 mt-md-0">
                                    <a href="{{ route('produtos.index') }}" class="btn btn-light btn-lg px-4 rounded-pill shadow">
                                        <i class="bi bi-grid"></i> Ver Produtos
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="carousel-item">
                        <div class="banner-slide" style="background: linear-gradient(135deg, #198754 0%, #157347 50%, #0f6840 100%); padding: 3rem 2rem; min-height: 200px;">
                            <div class="row align-items-center">
                                <div class="col-md-8 text-white">
                                    <h2 class="display-5 fw-bold"><i class="bi bi-fire"></i> Ofertas Imperdíveis!</h2>
                                    <p class="lead mb-0">Confira nossos produtos com preços especiais!</p>
                                </div>
                                <div class="col-md-4 text-md-end mt-3 mt-md-0">
                                    <a href="{{ route('produtos.index') }}" class="btn btn-warning btn-lg px-4 rounded-pill shadow fw-bold">
                                        <i class="bi bi-tags"></i> Ver Ofertas
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="carousel-item">
                        <div class="banner-slide" style="background: linear-gradient(135deg, #6f42c1 0%, #5e3a9e 50%, #4e2d85 100%); padding: 3rem 2rem; min-height: 200px;">
                            <div class="row align-items-center">
                                <div class="col-md-8 text-white">
                                    <h2 class="display-5 fw-bold"><i class="bi bi-star"></i> Novidades</h2>
                                    <p class="lead mb-0">Os lançamentos mais recentes em componentes!</p>
                                </div>
                                <div class="col-md-4 text-md-end mt-3 mt-md-0">
                                    <a href="{{ route('produtos.index') }}" class="btn btn-light btn-lg px-4 rounded-pill shadow">
                                        <i class="bi bi-newspaper"></i> Ver Novidades
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <button class="carousel-control-prev" type="button" data-bs-target="#bannerCarousel" data-bs-slide="prev">
                    <span class="carousel-control-prev-icon bg-dark rounded-circle p-2"></span>
                </button>
                <button class="carousel-control-next" type="button" data-bs-target="#bannerCarousel" data-bs-slide="next">
                    <span class="carousel-control-next-icon bg-dark rounded-circle p-2"></span>
                </button>
            </div>
        </div>
    </div>

    {{-- OFERTAS DO DIA --}}
    @if(isset($ofertas) && $ofertas->count() > 0)
    <div class="row mt-4">
        <div class="col-12">
            <h2 class="mb-3">🔥 Ofertas do Dia</h2>
        </div>
        @foreach($ofertas as $produto)
            <div class="col-lg-3 col-md-4 col-6 mb-4">
                <div class="card product-card h-100 shadow-sm border-warning">
                    <div class="position-relative">
                        <div class="card-img-top d-flex align-items-center justify-content-center bg-light" style="height: 180px;">
                            <i class="bi bi-plug" style="font-size: 3rem; color: #ddd;"></i>
                        </div>
                        <span class="badge bg-danger position-absolute top-0 start-0 m-2">OFERTA</span>
                    </div>
                    <div class="card-body d-flex flex-column">
                        <h6 class="card-title text-truncate">{{ Str::limit($produto->descricao, 35) }}</h6>
                        <p class="card-text mt-auto">
                            <span class="fw-bold text-danger">R$ {{ number_format($produto->preco_promocional ?? $produto->valor_unitario, 2, ',', '.') }}</span>
                        </p>
                        <small class="text-muted mb-2"><i class="bi bi-tag"></i> {{ $produto->categoria }}</small>
                        <a href="{{ route('produtos.show', $produto->slug) }}" class="btn btn-sm btn-outline-danger w-100">
                            <i class="bi bi-eye"></i> Ver Detalhes
                        </a>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
    @endif

    {{-- PRODUTOS EM DESTAQUE --}}
    @if(isset($produtosDestaque) && $produtosDestaque->count() > 0)
    <div class="row mt-4">
        <div class="col-12">
            <h2 class="mb-3">🌟 Produtos em Destaque</h2>
        </div>
        @foreach($produtosDestaque as $produto)
            <div class="col-lg-3 col-md-4 col-6 mb-4">
                <div class="card product-card h-100 shadow-sm">
                    <div class="position-relative">
                        <div class="card-img-top d-flex align-items-center justify-content-center bg-light" style="height: 180px;">
                            <i class="bi bi-plug" style="font-size: 3rem; color: #ddd;"></i>
                        </div>
                    </div>
                    <div class="card-body d-flex flex-column">
                        <h6 class="card-title text-truncate">{{ Str::limit($produto->descricao, 35) }}</h6>
                        <p class="card-text mt-auto">
                            <span class="fw-bold text-primary">R$ {{ number_format($produto->preco_atual ?? $produto->valor_unitario, 2, ',', '.') }}</span>
                        </p>
                        <small class="text-muted mb-2"><i class="bi bi-tag"></i> {{ $produto->categoria }}</small>
                        <a href="{{ route('produtos.show', $produto->slug) }}" class="btn btn-sm btn-outline-primary w-100">
                            <i class="bi bi-eye"></i> Ver Detalhes
                        </a>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
    @endif

    {{-- NOVOS PRODUTOS --}}
    @if(isset($novosProdutos) && $novosProdutos->count() > 0)
    <div class="row mt-4">
        <div class="col-12">
            <h2 class="mb-3">🆕 Novos Produtos</h2>
        </div>
        @foreach($novosProdutos as $produto)
            <div class="col-lg-3 col-md-4 col-6 mb-4">
                <div class="card product-card h-100 shadow-sm">
                    <div class="position-relative">
                        <div class="card-img-top d-flex align-items-center justify-content-center bg-light" style="height: 180px;">
                            <i class="bi bi-plug" style="font-size: 3rem; color: #ddd;"></i>
                        </div>
                        <span class="badge bg-info position-absolute top-0 start-0 m-2">NOVO</span>
                    </div>
                    <div class="card-body d-flex flex-column">
                        <h6 class="card-title text-truncate">{{ Str::limit($produto->descricao, 35) }}</h6>
                        <p class="card-text mt-auto">
                            <span class="fw-bold text-success">R$ {{ number_format($produto->preco_atual ?? $produto->valor_unitario, 2, ',', '.') }}</span>
                        </p>
                        <small class="text-muted mb-2"><i class="bi bi-tag"></i> {{ $produto->categoria }}</small>
                        <a href="{{ route('produtos.show', $produto->slug) }}" class="btn btn-sm btn-outline-success w-100">
                            <i class="bi bi-eye"></i> Ver Detalhes
                        </a>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
    @endif
</div>

<style>
    .banner-slide { border-radius: 12px; }
    .product-card { transition: transform 0.2s; }
    .product-card:hover { transform: translateY(-5px); }
</style>
@endsection