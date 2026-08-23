@extends('layouts.app')

@section('title', 'SM Componentes - Qualidade em Componentes Eletrônicos')

@section('content')

{{-- ============================================ --}}
{{-- CARROSSEL PRINCIPAL - COM BANNERS DO BANCO --}}
{{-- ============================================ --}}
<div class="carousel-principal">
    <div id="bannerCarousel" class="carousel slide" data-bs-ride="carousel">
        
        {{-- INDICADORES --}}
        @if(isset($banners) && $banners->count() > 1)
        <div class="carousel-indicators">
            @foreach($banners as $index => $banner)
            <button type="button" 
                    data-bs-target="#bannerCarousel" 
                    data-bs-slide-to="{{ $index }}" 
                    class="{{ $index === 0 ? 'active' : '' }}"
                    aria-current="{{ $index === 0 ? 'true' : 'false' }}"
                    aria-label="Slide {{ $index + 1 }}"></button>
            @endforeach
        </div>
        @endif

        {{-- SLIDES --}}
        <div class="carousel-inner">
            @if(isset($banners) && $banners->count() > 0)
                @foreach($banners as $index => $banner)
                <div class="carousel-item {{ $index === 0 ? 'active' : '' }}">
                    
                    {{-- BANNER COM IMAGEM --}}
                    @if(isset($banner->imagem_url) && $banner->imagem_url)
                    <div class="position-relative">
                        <img class="d-block w-100" 
                             src="{{ $banner->imagem_url }}" 
                             alt="{{ $banner->titulo ?? 'Banner' }}"
                             style="height: 450px; object-fit: cover;">
                        
                        {{-- OVERLAY COM TEXTO --}}
                        @if(isset($banner->titulo) || isset($banner->subtitulo) || isset($banner->descricao) || isset($banner->texto_botao))
                        <div class="banner-overlay d-flex align-items-center">
                            <div class="container">
                                <div class="row">
                                    <div class="col-lg-6 col-md-8">
                                        <div class="banner-content">
                                            @if(isset($banner->titulo) && $banner->titulo)
                                                <h1 class="display-4 fw-bold mb-3" 
                                                    style="text-shadow: 2px 2px 10px rgba(0,0,0,0.5); color: {{ $banner->cor_texto ?? '#ffffff' }};">
                                                    {{ $banner->titulo }}
                                                </h1>
                                            @endif
                                            
                                            @if(isset($banner->subtitulo) && $banner->subtitulo)
                                                <h4 class="fw-semibold mb-3" 
                                                    style="text-shadow: 2px 2px 10px rgba(0,0,0,0.5); color: {{ $banner->cor_texto ?? '#ffffff' }};">
                                                    {{ $banner->subtitulo }}
                                                </h4>
                                            @endif
                                            
                                            @if(isset($banner->descricao) && $banner->descricao)
                                                <p class="lead mb-4" 
                                                    style="text-shadow: 2px 2px 10px rgba(0,0,0,0.5); color: {{ $banner->cor_texto ?? '#ffffff' }};">
                                                    {{ $banner->descricao }}
                                                </p>
                                            @endif
                                            
                                            @if(isset($banner->link) && isset($banner->texto_botao) && $banner->link && $banner->texto_botao)
                                                <a href="{{ $banner->link }}" 
                                                   class="btn btn-{{ $banner->cor_botao ?? 'primary' }} btn-lg rounded-pill px-5 py-3 fw-bold shadow-lg"
                                                   style="transition: all 0.3s ease;"
                                                   @if(str_starts_with($banner->link, 'http')) target="_blank" rel="noopener noreferrer" @endif>
                                                    {{ $banner->texto_botao }} <i class="bi bi-arrow-right ms-2"></i>
                                                </a>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>
                    @else
                    {{-- BANNER SEM IMAGEM (FALLBACK) --}}
                    <div class="banner-placeholder d-flex align-items-center justify-content-center text-center" 
                         style="height: 450px; {{ $banner->estilo_fundo ?? 'background: linear-gradient(135deg, #0b1a33 0%, #1a3a5c 100%);' }}">
                        <div class="text-white p-4" style="max-width: 700px; color: {{ $banner->cor_texto ?? '#ffffff' }};">
                            @if(isset($banner->titulo) && $banner->titulo)
                                <h1 class="display-3 fw-bold mb-3">{{ $banner->titulo }}</h1>
                            @endif
                            @if(isset($banner->subtitulo) && $banner->subtitulo)
                                <h4 class="fw-semibold mb-3">{{ $banner->subtitulo }}</h4>
                            @endif
                            @if(isset($banner->descricao) && $banner->descricao)
                                <p class="lead mb-4">{{ $banner->descricao }}</p>
                            @endif
                            @if(isset($banner->link) && isset($banner->texto_botao) && $banner->link && $banner->texto_botao)
                                <a href="{{ $banner->link }}" 
                                   class="btn btn-{{ $banner->cor_botao ?? 'light' }} btn-lg rounded-pill px-5 py-3 fw-bold shadow-lg"
                                   @if(str_starts_with($banner->link, 'http')) target="_blank" rel="noopener noreferrer" @endif>
                                    {{ $banner->texto_botao }} <i class="bi bi-arrow-right ms-2"></i>
                                </a>
                            @endif
                        </div>
                    </div>
                    @endif
                </div>
                @endforeach
            @else
                {{-- BANNER PADRÃO --}}
                <div class="carousel-item active">
                    <div class="banner-placeholder d-flex align-items-center justify-content-center text-center" 
                         style="height: 450px; background: linear-gradient(135deg, #0b1a33 0%, #1a3a5c 100%);">
                        <div class="text-white p-4" style="max-width: 700px;">
                            <h1 class="display-3 fw-bold mb-3">SM Componentes</h1>
                            <h4 class="fw-semibold mb-3 text-warning">Qualidade em Componentes Eletrônicos</h4>
                            <p class="lead mb-4">Encontre os melhores componentes para seus projetos</p>
                            <a href="{{ route('produtos.index') }}" class="btn btn-light btn-lg rounded-pill px-5 py-3 fw-bold shadow-lg">
                                Ver Produtos <i class="bi bi-arrow-right ms-2"></i>
                            </a>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        {{-- CONTROLES --}}
        @if(isset($banners) && $banners->count() > 1)
        <button class="carousel-control-prev" type="button" data-bs-target="#bannerCarousel" data-bs-slide="prev">
            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Anterior</span>
        </button>
        <button class="carousel-control-next" type="button" data-bs-target="#bannerCarousel" data-bs-slide="next">
            <span class="carousel-control-next-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Próximo</span>
        </button>
        @endif
    </div>
</div>

{{-- ============================================ --}}
{{-- VANTAGENS - SMART KITS                     --}}
{{-- ============================================ --}}
<section class="vantagens-smart py-4">
    <div class="container">
        <div class="row g-3">
            <div class="col-6 col-md-4 col-lg-2">
                <div class="vantagem-card text-center p-3">
                    <i class="bi bi-truck fs-2 text-primary"></i>
                    <h6 class="mt-2 fw-bold">POSTAGEM RÁPIDA</h6>
                    <small class="text-muted">Enviamos em até 24h</small>
                </div>
            </div>
            <div class="col-6 col-md-4 col-lg-2">
                <div class="vantagem-card text-center p-3">
                    <i class="bi bi-credit-card fs-2 text-success"></i>
                    <h6 class="mt-2 fw-bold">PARCELAMENTO</h6>
                    <small class="text-muted">Em até 6x sem juros</small>
                </div>
            </div>
            <div class="col-6 col-md-4 col-lg-2">
                <div class="vantagem-card text-center p-3">
                    <i class="bi bi-whatsapp fs-2 text-success"></i>
                    <h6 class="mt-2 fw-bold">ATENDIMENTO</h6>
                    <small class="text-muted">Via WhatsApp</small>
                </div>
            </div>
            <div class="col-6 col-md-4 col-lg-2">
                <div class="vantagem-card text-center p-3">
                    <i class="bi bi-bicycle fs-2 text-warning"></i>
                    <h6 class="mt-2 fw-bold">MOTOBOY</h6>
                    <small class="text-muted">Fortaleza e região</small>
                </div>
            </div>
            <div class="col-6 col-md-4 col-lg-2">
                <div class="vantagem-card text-center p-3">
                    <i class="bi bi-shield-lock fs-2 text-danger"></i>
                    <h6 class="mt-2 fw-bold">SITE 100% SEGURO</h6>
                    <small class="text-muted">Compre com segurança</small>
                </div>
            </div>
            <div class="col-6 col-md-4 col-lg-2">
                <div class="vantagem-card text-center p-3">
                    <i class="bi bi-arrow-repeat fs-2 text-info"></i>
                    <h6 class="mt-2 fw-bold">GARANTIA</h6>
                    <small class="text-muted">7 dias de satisfação</small>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ============================================ --}}
{{-- PRODUTOS EM DESTAQUE - CORRIGIDO            --}}
{{-- ============================================ --}}
@if(isset($produtosDestaque) && $produtosDestaque->count() > 0)
<section class="produtos-smart py-4">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h3 class="fw-bold mb-0">🌟 Produtos em Destaque</h3>
            <a href="{{ route('produtos.index') }}" class="btn btn-outline-primary btn-sm rounded-pill">
                Ver Todos <i class="bi bi-arrow-right ms-1"></i>
            </a>
        </div>
        <div class="row g-3">
            @foreach($produtosDestaque as $produto)
                <div class="col-lg-3 col-md-4 col-6">
                    <div class="card produto-card-smart h-100 shadow-sm border-0">
                        <div class="position-relative">
                            <div class="card-img-top d-flex align-items-center justify-content-center bg-light" style="height: 180px; overflow: hidden;">
                                @if($produto->imagem)
                                    <img src="{{ $produto->imagem_url }}" 
                                         alt="{{ $produto->descricao ?? 'Produto' }}"
                                         class="img-fluid"
                                         style="width: 100%; height: 100%; object-fit: cover;"
                                         onerror="this.onerror=null; this.src='{{ asset('images/produto-placeholder.jpg') }}';">
                                @else
                                    <i class="bi bi-plug fs-1 text-muted"></i>
                                @endif
                            </div>
                            @if($produto->tem_promocao)
                                <span class="badge bg-danger position-absolute top-0 end-0 m-2 rounded-pill">
                                    -{{ $produto->desconto_percentual }}%
                                </span>
                            @endif
                        </div>
                        <div class="card-body d-flex flex-column p-3">
                            <h6 class="card-title text-truncate">{{ Str::limit($produto->descricao ?? 'Produto', 30) }}</h6>
                            <p class="card-text mt-auto">
                                @if($produto->tem_promocao)
                                    <span class="text-decoration-line-through text-muted me-1 small">
                                        {{ $produto->preco_atacado_formatado }}
                                    </span>
                                    <span class="fw-bold text-danger">
                                        {{ $produto->preco_promocional_formatado }}
                                    </span>
                                @else
                                    <span class="fw-bold text-primary">
                                        {{ $produto->preco_atacado_formatado }}
                                    </span>
                                @endif
                            </p>
                            @if($produto->pode_comprar)
                                <a href="{{ route('produtos.show', $produto->slug ?? '#') }}" 
                                   class="btn btn-sm btn-outline-primary w-100 rounded-pill">
                                    <i class="bi bi-eye me-1"></i> Ver Detalhes
                                </a>
                            @else
                                <button class="btn btn-sm btn-secondary w-100 rounded-pill" disabled>
                                    <i class="bi bi-x-circle me-1"></i> Indisponível
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
        
        @if($produtosDestaque->hasPages())
        <div class="row mt-3">
            <div class="col-12">
                <div class="d-flex justify-content-center">
                    {{ $produtosDestaque->appends(request()->except(['page_destaque', 'page_ofertas', 'page_novos', 'page_vendidos', 'page_todos']))->links('pagination::bootstrap-5') }}
                </div>
            </div>
        </div>
        @endif
    </div>
</section>
@endif

{{-- ============================================ --}}
{{-- OFERTAS DO DIA - CORRIGIDO                 --}}
{{-- ============================================ --}}
@if(isset($ofertas) && $ofertas->count() > 0)
<section class="ofertas-smart py-4 bg-light">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h3 class="fw-bold mb-0">🔥 Ofertas do Dia</h3>
            <a href="{{ route('produtos.index') }}" class="btn btn-outline-danger btn-sm rounded-pill">
                Ver Ofertas <i class="bi bi-arrow-right ms-1"></i>
            </a>
        </div>
        <div class="row g-3">
            @foreach($ofertas as $produto)
                <div class="col-lg-3 col-md-4 col-6">
                    <div class="card produto-card-smart h-100 shadow-sm border-0 border-warning border-2">
                        <div class="position-relative">
                            <div class="card-img-top d-flex align-items-center justify-content-center bg-light" style="height: 180px; overflow: hidden;">
                                @if($produto->imagem)
                                    <img src="{{ $produto->imagem_url }}" 
                                         alt="{{ $produto->descricao ?? 'Produto' }}"
                                         class="img-fluid"
                                         style="width: 100%; height: 100%; object-fit: cover;"
                                         onerror="this.onerror=null; this.src='{{ asset('images/produto-placeholder.jpg') }}';">
                                @else
                                    <i class="bi bi-plug fs-1 text-muted"></i>
                                @endif
                            </div>
                            @if($produto->tem_promocao)
                                <span class="badge bg-danger position-absolute top-0 start-0 m-2 rounded-pill">
                                    -{{ $produto->desconto_percentual }}% OFF
                                </span>
                            @endif
                        </div>
                        <div class="card-body d-flex flex-column p-3">
                            <h6 class="card-title text-truncate">{{ Str::limit($produto->descricao ?? 'Produto', 30) }}</h6>
                            <p class="card-text mt-auto">
                                @if($produto->tem_promocao)
                                    <span class="text-decoration-line-through text-muted me-1 small">
                                        {{ $produto->preco_atacado_formatado }}
                                    </span>
                                    <span class="fw-bold text-danger">
                                        {{ $produto->preco_promocional_formatado }}
                                    </span>
                                @else
                                    <span class="fw-bold text-danger">
                                        {{ $produto->preco_atacado_formatado }}
                                    </span>
                                @endif
                            </p>
                            @if($produto->pode_comprar)
                                <a href="{{ route('produtos.show', $produto->slug ?? '#') }}" 
                                   class="btn btn-sm btn-outline-danger w-100 rounded-pill">
                                    <i class="bi bi-eye me-1"></i> Ver Detalhes
                                </a>
                            @else
                                <button class="btn btn-sm btn-secondary w-100 rounded-pill" disabled>
                                    <i class="bi bi-x-circle me-1"></i> Indisponível
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
        
        @if($ofertas->hasPages())
        <div class="row mt-3">
            <div class="col-12">
                <div class="d-flex justify-content-center">
                    {{ $ofertas->appends(request()->except(['page_destaque', 'page_ofertas', 'page_novos', 'page_vendidos', 'page_todos']))->links('pagination::bootstrap-5') }}
                </div>
            </div>
        </div>
        @endif
    </div>
</section>
@endif

{{-- ============================================ --}}
{{-- MAIS VENDIDOS - CORRIGIDO                  --}}
{{-- ============================================ --}}
@if(isset($maisVendidos) && $maisVendidos->count() > 0)
<section class="mais-vendidos-smart py-4">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h3 class="fw-bold mb-0">🏆 Mais Vendidos</h3>
            <a href="{{ route('produtos.index') }}" class="btn btn-outline-warning btn-sm rounded-pill">
                Ver Mais <i class="bi bi-arrow-right ms-1"></i>
            </a>
        </div>
        <div class="row g-3">
            @foreach($maisVendidos as $produto)
                <div class="col-lg-3 col-md-4 col-6">
                    <div class="card produto-card-smart h-100 shadow-sm border-0">
                        <div class="position-relative">
                            <div class="card-img-top d-flex align-items-center justify-content-center bg-light" style="height: 180px; overflow: hidden;">
                                @if($produto->imagem)
                                    <img src="{{ $produto->imagem_url }}" 
                                         alt="{{ $produto->descricao ?? 'Produto' }}"
                                         class="img-fluid"
                                         style="width: 100%; height: 100%; object-fit: cover;"
                                         onerror="this.onerror=null; this.src='{{ asset('images/produto-placeholder.jpg') }}';">
                                @else
                                    <i class="bi bi-plug fs-1 text-muted"></i>
                                @endif
                            </div>
                            <span class="badge bg-warning position-absolute top-0 start-0 m-2 rounded-pill">
                                <i class="bi bi-trophy me-1"></i> TOP
                            </span>
                        </div>
                        <div class="card-body d-flex flex-column p-3">
                            <h6 class="card-title text-truncate">{{ Str::limit($produto->descricao ?? 'Produto', 30) }}</h6>
                            <p class="card-text mt-auto">
                                @if($produto->tem_promocao)
                                    <span class="text-decoration-line-through text-muted me-1 small">
                                        {{ $produto->preco_atacado_formatado }}
                                    </span>
                                    <span class="fw-bold text-warning">
                                        {{ $produto->preco_promocional_formatado }}
                                    </span>
                                @else
                                    <span class="fw-bold text-warning">
                                        {{ $produto->preco_atacado_formatado }}
                                    </span>
                                @endif
                            </p>
                            @if($produto->pode_comprar)
                                <a href="{{ route('produtos.show', $produto->slug ?? '#') }}" 
                                   class="btn btn-sm btn-outline-warning w-100 rounded-pill">
                                    <i class="bi bi-eye me-1"></i> Ver Detalhes
                                </a>
                            @else
                                <button class="btn btn-sm btn-secondary w-100 rounded-pill" disabled>
                                    <i class="bi bi-x-circle me-1"></i> Indisponível
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
        
        @if($maisVendidos->hasPages())
        <div class="row mt-3">
            <div class="col-12">
                <div class="d-flex justify-content-center">
                    {{ $maisVendidos->appends(request()->except(['page_destaque', 'page_ofertas', 'page_novos', 'page_vendidos', 'page_todos']))->links('pagination::bootstrap-5') }}
                </div>
            </div>
        </div>
        @endif
    </div>
</section>
@endif

{{-- ============================================ --}}
{{-- TODOS OS PRODUTOS - CORRIGIDO               --}}
{{-- ============================================ --}}
@if(isset($produtosDisponiveis) && $produtosDisponiveis->count() > 0)
<section class="todos-produtos-smart py-4 bg-light">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h3 class="fw-bold mb-0">📦 Todos os Produtos</h3>
            <a href="{{ route('produtos.index') }}" class="btn btn-outline-primary btn-sm rounded-pill">
                Ver Todos <i class="bi bi-arrow-right ms-1"></i>
            </a>
        </div>
        <p class="text-muted small mb-3">
            Mostrando {{ $produtosDisponiveis->firstItem() ?? 0 }} a {{ $produtosDisponiveis->lastItem() ?? 0 }} de {{ $produtosDisponiveis->total() }} produtos
        </p>
        <div class="row g-3">
            @foreach($produtosDisponiveis as $produto)
                <div class="col-lg-3 col-md-4 col-6">
                    <div class="card produto-card-smart h-100 shadow-sm border-0">
                        <div class="position-relative">
                            <div class="card-img-top d-flex align-items-center justify-content-center bg-light" style="height: 180px; overflow: hidden;">
                                @if($produto->imagem)
                                    <img src="{{ $produto->imagem_url }}" 
                                         alt="{{ $produto->descricao ?? 'Produto' }}"
                                         class="img-fluid"
                                         style="width: 100%; height: 100%; object-fit: cover;"
                                         onerror="this.onerror=null; this.src='{{ asset('images/produto-placeholder.jpg') }}';">
                                @else
                                    <i class="bi bi-plug fs-1 text-muted"></i>
                                @endif
                            </div>
                            @if($produto->tem_promocao)
                                <span class="badge bg-danger position-absolute top-0 end-0 m-2 rounded-pill">
                                    -{{ $produto->desconto_percentual }}%
                                </span>
                            @endif
                        </div>
                        <div class="card-body d-flex flex-column p-3">
                            <h6 class="card-title text-truncate">{{ Str::limit($produto->descricao ?? 'Produto', 30) }}</h6>
                            <p class="card-text mt-auto">
                                @if($produto->tem_promocao)
                                    <span class="text-decoration-line-through text-muted me-1 small">
                                        {{ $produto->preco_atacado_formatado }}
                                    </span>
                                    <span class="fw-bold text-primary">
                                        {{ $produto->preco_promocional_formatado }}
                                    </span>
                                @else
                                    <span class="fw-bold text-primary">
                                        {{ $produto->preco_atacado_formatado }}
                                    </span>
                                @endif
                            </p>
                            @if($produto->pode_comprar)
                                <a href="{{ route('produtos.show', $produto->slug ?? '#') }}" 
                                   class="btn btn-sm btn-outline-primary w-100 rounded-pill">
                                    <i class="bi bi-eye me-1"></i> Ver Detalhes
                                </a>
                            @else
                                <button class="btn btn-sm btn-secondary w-100 rounded-pill" disabled>
                                    <i class="bi bi-x-circle me-1"></i> Indisponível
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
        
        @if($produtosDisponiveis->hasPages())
        <div class="row mt-3">
            <div class="col-12">
                <div class="d-flex justify-content-center">
                    {{ $produtosDisponiveis->appends(request()->except(['page_destaque', 'page_ofertas', 'page_novos', 'page_vendidos', 'page_todos']))->links('pagination::bootstrap-5') }}
                </div>
            </div>
        </div>
        @endif
    </div>
</section>
@endif

{{-- ============================================ --}}
{{-- STYLES - SMART KITS                        --}}
{{-- ============================================ --}}
@push('styles')
<style>
    /* ============================================ */
    /* CARROSSEL - LARGURA TOTAL                  */
    /* ============================================ */
    .carousel-principal {
        width: 100%;
        overflow: hidden;
        margin-bottom: 0;
    }

    .carousel-principal .carousel-item img {
        height: 450px;
        object-fit: cover;
        width: 100%;
        display: block;
    }

    .carousel-principal .carousel-indicators {
        margin-bottom: 10px;
    }

    .carousel-principal .carousel-indicators button {
        width: 12px !important;
        height: 12px !important;
        border-radius: 50% !important;
        border: 2px solid rgba(255,255,255,0.8) !important;
        margin: 0 5px !important;
        background: transparent !important;
        transition: all 0.3s ease;
    }

    .carousel-principal .carousel-indicators .active {
        background: #ffffff !important;
        border-color: #ffffff !important;
        transform: scale(1.15);
    }

    .carousel-principal .carousel-control-prev,
    .carousel-principal .carousel-control-next {
        width: 44px;
        height: 44px;
        top: 50%;
        transform: translateY(-50%);
        opacity: 0;
        transition: all 0.3s ease;
        border-radius: 50%;
        background: rgba(0,0,0,0.25);
    }

    .carousel-principal:hover .carousel-control-prev,
    .carousel-principal:hover .carousel-control-next {
        opacity: 1;
    }

    .carousel-principal .carousel-control-prev {
        left: 16px;
    }

    .carousel-principal .carousel-control-next {
        right: 16px;
    }

    .carousel-principal .carousel-control-prev:hover,
    .carousel-principal .carousel-control-next:hover {
        background: rgba(0,0,0,0.5);
        transform: translateY(-50%) scale(1.05);
    }

    .carousel-principal .banner-placeholder {
        height: 450px;
        display: flex;
        align-items: center;
        justify-content: center;
        text-align: center;
    }

    /* ============================================ */
    /* OVERLAY DO BANNER                          */
    /* ============================================ */
    .banner-overlay {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: linear-gradient(90deg, rgba(0,0,0,0.7) 0%, rgba(0,0,0,0.2) 100%);
        padding: 2rem;
    }

    .banner-content h1 {
        font-size: 3rem;
        line-height: 1.2;
        letter-spacing: -0.5px;
    }

    .banner-content .lead {
        font-size: 1.25rem;
        opacity: 0.95;
    }

    .banner-content .btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(0, 102, 204, 0.4) !important;
    }

    /* ============================================ */
    /* VANTAGENS - SMART KITS                     */
    /* ============================================ */
    .vantagem-card {
        transition: all 0.2s ease;
        border-radius: 8px;
        background: white;
        border: 1px solid #e2e8f0;
    }
    .vantagem-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 15px rgba(0,0,0,0.06);
        border-color: #2563eb;
    }
    .vantagem-card i {
        font-size: 2.2rem;
    }
    .vantagem-card h6 {
        font-size: 0.8rem;
        margin-bottom: 2px;
    }
    .vantagem-card small {
        font-size: 0.7rem;
    }

    /* ============================================ */
    /* PRODUTOS CARD - SMART KITS                 */
    /* ============================================ */
    .produto-card-smart {
        transition: transform 0.2s ease, box-shadow 0.2s ease;
        border-radius: 12px !important;
        overflow: hidden;
    }
    .produto-card-smart:hover {
        transform: translateY(-4px);
        box-shadow: 0 8px 25px rgba(0,0,0,0.08) !important;
    }
    .produto-card-smart .card-img-top {
        background: #f8f9fa;
        padding: 0;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .produto-card-smart .card-img-top img {
        display: block;
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    .produto-card-smart .card-img-top i {
        font-size: 3rem;
        color: #adb5bd;
    }
    .produto-card-smart .card-body {
        padding: 10px 12px 12px;
    }
    .produto-card-smart .card-title {
        font-size: 0.82rem;
        font-weight: 600;
        min-height: 38px;
        margin-bottom: 4px;
    }
    .produto-card-smart .btn {
        font-size: 0.78rem;
        padding: 5px 10px;
    }

    /* ============================================ */
    /* RESPONSIVO                                 */
    /* ============================================ */
    @media (max-width: 992px) {
        .carousel-principal .carousel-item img {
            height: 350px;
        }
        .carousel-principal .banner-placeholder {
            height: 350px;
        }
        .banner-content h1 {
            font-size: 2.5rem;
        }
        .banner-content .lead {
            font-size: 1rem;
        }
    }

    @media (max-width: 768px) {
        .carousel-principal .carousel-item img {
            height: 250px;
        }
        .carousel-principal .banner-placeholder {
            height: 250px;
        }
        .carousel-principal .banner-placeholder h1 {
            font-size: 1.8rem !important;
        }
        .carousel-principal .banner-placeholder p {
            font-size: 0.9rem !important;
        }
        .carousel-principal .carousel-control-prev,
        .carousel-principal .carousel-control-next {
            width: 34px;
            height: 34px;
        }
        .banner-overlay {
            padding: 1.5rem;
            background: linear-gradient(90deg, rgba(0,0,0,0.8) 0%, rgba(0,0,0,0.3) 100%);
        }
        .banner-content h1 {
            font-size: 2rem;
        }
        .banner-content h4 {
            font-size: 1.2rem;
        }
        .banner-content .lead {
            font-size: 0.9rem;
        }
        .banner-content .btn {
            font-size: 0.9rem !important;
            padding: 0.6rem 1.8rem !important;
        }
        .vantagem-card {
            padding: 0.75rem !important;
        }
        .vantagem-card i {
            font-size: 1.5rem;
        }
        .vantagem-card h6 {
            font-size: 0.65rem;
        }
        .vantagem-card small {
            font-size: 0.6rem;
        }
        .produto-card-smart .card-img-top {
            height: 140px !important;
        }
        .produto-card-smart .card-title {
            font-size: 0.75rem;
            min-height: 32px;
        }
        .produto-card-smart .btn {
            font-size: 0.7rem;
            padding: 4px 8px;
        }
        .produto-card-smart .card-body {
            padding: 8px 10px 10px;
        }
        .produtos-smart h3,
        .ofertas-smart h3,
        .mais-vendidos-smart h3,
        .todos-produtos-smart h3 {
            font-size: 1.1rem;
        }
    }

    @media (max-width: 576px) {
        .carousel-principal .carousel-item img {
            height: 180px;
        }
        .carousel-principal .banner-placeholder {
            height: 180px;
        }
        .carousel-principal .banner-placeholder h1 {
            font-size: 1.2rem !important;
        }
        .carousel-principal .banner-placeholder p {
            font-size: 0.7rem !important;
        }
        .carousel-principal .carousel-control-prev,
        .carousel-principal .carousel-control-next {
            width: 28px;
            height: 28px;
            opacity: 1 !important;
        }
        .carousel-principal .carousel-control-prev {
            left: 6px !important;
        }
        .carousel-principal .carousel-control-next {
            right: 6px !important;
        }
        .carousel-principal .carousel-control-prev-icon,
        .carousel-principal .carousel-control-next-icon {
            width: 16px !important;
            height: 16px !important;
            background-size: 40% 40%;
        }
        .carousel-principal .carousel-indicators button {
            width: 8px !important;
            height: 8px !important;
        }
        .banner-overlay {
            padding: 1rem;
            background: linear-gradient(90deg, rgba(0,0,0,0.85) 0%, rgba(0,0,0,0.3) 100%);
        }
        .banner-content h1 {
            font-size: 1.5rem;
        }
        .banner-content h4 {
            font-size: 1rem;
        }
        .banner-content .lead {
            font-size: 0.8rem;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        .banner-content .btn {
            font-size: 0.8rem !important;
            padding: 0.4rem 1.2rem !important;
        }
        .vantagem-card {
            padding: 0.5rem !important;
        }
        .vantagem-card i {
            font-size: 1.2rem;
        }
        .vantagem-card h6 {
            font-size: 0.6rem;
        }
        .vantagem-card small {
            font-size: 0.55rem;
        }
        .produto-card-smart .card-img-top {
            height: 110px !important;
        }
        .produto-card-smart .card-title {
            font-size: 0.7rem;
            min-height: 28px;
        }
        .produto-card-smart .card-body {
            padding: 6px 8px 8px;
        }
        .produto-card-smart .btn {
            font-size: 0.65rem;
            padding: 3px 6px;
        }
        .produto-card-smart .card-text {
            font-size: 0.8rem;
        }
        .produtos-smart h3,
        .ofertas-smart h3,
        .mais-vendidos-smart h3,
        .todos-produtos-smart h3 {
            font-size: 0.95rem;
        }
        .produtos-smart .btn-sm,
        .ofertas-smart .btn-sm,
        .mais-vendidos-smart .btn-sm,
        .todos-produtos-smart .btn-sm {
            font-size: 0.7rem;
            padding: 3px 10px;
        }
        .vantagens-smart .col-6,
        .produtos-smart .col-6,
        .ofertas-smart .col-6,
        .mais-vendidos-smart .col-6,
        .todos-produtos-smart .col-6 {
            padding: 0 4px;
        }
        .pagination .page-link {
            padding: 0.3rem 0.6rem;
            font-size: 0.75rem;
            min-width: 1.8rem;
        }
    }
</style>
@endpush

@endsection