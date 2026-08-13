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

    {{-- ============================================ --}}
    {{-- CARROSSEL DE BANNERS --}}
    {{-- ============================================ --}}
    @if(isset($banners) && $banners->count() > 0)
    <div class="row mb-4">
        <div class="col-12">
            <div id="bannerCarousel" class="carousel slide" data-bs-ride="carousel">
                {{-- INDICADORES --}}
                @if($banners->count() > 1)
                <div class="carousel-indicators">
                    @foreach($banners as $banner)
                    <button type="button" 
                            data-bs-target="#bannerCarousel" 
                            data-bs-slide-to="{{ $loop->index }}" 
                            class="{{ $loop->first ? 'active' : '' }}"
                            aria-label="Slide {{ $loop->iteration }}">
                    </button>
                    @endforeach
                </div>
                @endif

                {{-- SLIDES --}}
                <div class="carousel-inner rounded-4 shadow">
                    @foreach($banners as $banner)
                    <div class="carousel-item {{ $loop->first ? 'active' : '' }}">
                        <div class="banner-slide" 
                             style="
                                {{ $banner->estilo_fundo ?? 'background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 50%, #084298 100%);' }}
                                padding: 3rem 2rem;
                                min-height: 250px;
                                position: relative;
                                overflow: hidden;
                                border-radius: 12px;
                             ">
                            
                            {{-- IMAGEM DE FUNDO --}}
                            @if(isset($banner->imagem_url) && $banner->imagem_url && $banner->tipo != 'texto')
                            <div style="
                                position: absolute;
                                top: 0;
                                left: 0;
                                width: 100%;
                                height: 100%;
                                background-image: url('{{ $banner->imagem_url }}');
                                background-size: cover;
                                background-position: center;
                                opacity: 0.85;
                                z-index: 0;
                            "></div>
                            @endif

                            {{-- CONTEÚDO --}}
                            <div class="row align-items-center position-relative" style="z-index: 1; min-height: 150px;">
                                <div class="{{ isset($banner->link) && $banner->link ? 'col-md-8' : 'col-12' }} 
                                            text-{{ isset($banner->cor_texto) && $banner->cor_texto ? '' : 'white' }}" 
                                     style="color: {{ $banner->cor_texto ?? '#ffffff' }};">
                                    
                                    @if($banner->titulo)
                                        @if($loop->first)
                                            <h1 class="display-5 fw-bold">{{ $banner->titulo }}</h1>
                                        @else
                                            <h2 class="display-5 fw-bold">{!! $banner->titulo !!}</h2>
                                        @endif
                                    @endif
                                    
                                    @if($banner->subtitulo)
                                    <h3 class="fw-semibold">{{ $banner->subtitulo }}</h3>
                                    @endif
                                    
                                    @if($banner->descricao)
                                    <p class="lead mb-0">{{ $banner->descricao }}</p>
                                    @endif
                                </div>

                                {{-- BOTÃO --}}
                                @if(isset($banner->link) && $banner->link && isset($banner->texto_botao) && $banner->texto_botao)
                                <div class="col-md-4 text-md-end mt-3 mt-md-0">
                                    <a href="{{ $banner->link }}" 
                                       class="btn btn-{{ $banner->cor_botao ?? 'light' }} btn-lg px-4 rounded-pill shadow fw-bold"
                                       @if(str_starts_with($banner->link, 'http')) target="_blank" @endif>
                                        <i class="bi bi-arrow-right"></i> {{ $banner->texto_botao }}
                                    </a>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>

                {{-- CONTROLES --}}
                @if($banners->count() > 1)
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
    </div>
    @endif

    {{-- ============================================ --}}
    {{-- PRODUTOS EM DESTAQUE --}}
    {{-- ============================================ --}}
    @if(isset($produtosDestaque) && $produtosDestaque->count() > 0)
    <div class="row mt-4">
        <div class="col-12">
            <h2 class="mb-3">🌟 Produtos em Destaque</h2>
        </div>
        @foreach($produtosDestaque as $produto)
            <div class="col-lg-3 col-md-4 col-6 mb-4">
                <div class="card product-card h-100 shadow-sm">
                    <div class="position-relative">
                        <div class="card-img-top d-flex align-items-center justify-content-center bg-light" style="height: 200px;">
                            @if($produto->imagem)
                                <img src="{{ asset('storage/' . $produto->imagem) }}" 
                                     alt="{{ $produto->descricao }}"
                                     style="height: 100%; width: 100%; object-fit: cover;">
                            @else
                                <i class="bi bi-plug fs-1 text-muted"></i>
                            @endif
                        </div>
                        @if($produto->preco_promocional && $produto->preco_promocional > 0)
                            <span class="badge bg-danger position-absolute top-0 end-0 m-2">OFERTA</span>
                        @endif
                    </div>
                    <div class="card-body d-flex flex-column">
                        <h6 class="card-title text-truncate">{{ Str::limit($produto->descricao, 35) }}</h6>
                        <p class="card-text mt-auto">
                            @if($produto->preco_promocional && $produto->preco_promocional > 0)
                                <span class="text-decoration-line-through text-muted me-1">
                                    R$ {{ number_format($produto->valor_unitario, 2, ',', '.') }}
                                </span>
                                <span class="fw-bold text-danger">
                                    R$ {{ number_format($produto->preco_promocional, 2, ',', '.') }}
                                </span>
                            @else
                                <span class="fw-bold text-primary">
                                    R$ {{ number_format($produto->valor_unitario, 2, ',', '.') }}
                                </span>
                            @endif
                        </p>
                        <small class="text-muted mb-2"><i class="bi bi-tag"></i> {{ $produto->categoria ?? 'Geral' }}</small>
                        <a href="{{ route('produtos.show', $produto->slug) }}" class="btn btn-sm btn-outline-primary w-100">
                            <i class="bi bi-eye"></i> Ver Detalhes
                        </a>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    {{-- PAGINAÇÃO --}}
    @if($produtosDestaque->hasPages())
    <div class="row mt-2">
        <div class="col-12">
            <div class="d-flex justify-content-center">
                {{ $produtosDestaque->appends(request()->except(['page_destaque', 'page_ofertas', 'page_novos', 'page_vendidos', 'page_todos']))->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </div>
    @endif
    @endif

    {{-- ============================================ --}}
    {{-- OFERTAS DO DIA --}}
    {{-- ============================================ --}}
    @if(isset($ofertas) && $ofertas->count() > 0)
    <div class="row mt-4">
        <div class="col-12">
            <h2 class="mb-3">🔥 Ofertas do Dia</h2>
        </div>
        @foreach($ofertas as $produto)
            <div class="col-lg-3 col-md-4 col-6 mb-4">
                <div class="card product-card h-100 shadow-sm border-warning">
                    <div class="position-relative">
                        <div class="card-img-top d-flex align-items-center justify-content-center bg-light" style="height: 200px;">
                            @if($produto->imagem)
                                <img src="{{ asset('storage/' . $produto->imagem) }}" 
                                     alt="{{ $produto->descricao }}"
                                     style="height: 100%; width: 100%; object-fit: cover;">
                            @else
                                <i class="bi bi-plug fs-1 text-muted"></i>
                            @endif
                        </div>
                        <span class="badge bg-danger position-absolute top-0 start-0 m-2">OFERTA</span>
                    </div>
                    <div class="card-body d-flex flex-column">
                        <h6 class="card-title text-truncate">{{ Str::limit($produto->descricao, 35) }}</h6>
                        <p class="card-text mt-auto">
                            <span class="text-decoration-line-through text-muted me-1">
                                R$ {{ number_format($produto->valor_unitario, 2, ',', '.') }}
                            </span>
                            <span class="fw-bold text-danger">
                                R$ {{ number_format($produto->preco_promocional, 2, ',', '.') }}
                            </span>
                        </p>
                        <small class="text-muted mb-2"><i class="bi bi-tag"></i> {{ $produto->categoria ?? 'Geral' }}</small>
                        <a href="{{ route('produtos.show', $produto->slug) }}" class="btn btn-sm btn-outline-danger w-100">
                            <i class="bi bi-eye"></i> Ver Detalhes
                        </a>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    {{-- PAGINAÇÃO --}}
    @if($ofertas->hasPages())
    <div class="row mt-2">
        <div class="col-12">
            <div class="d-flex justify-content-center">
                {{ $ofertas->appends(request()->except(['page_destaque', 'page_ofertas', 'page_novos', 'page_vendidos', 'page_todos']))->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </div>
    @endif
    @endif

    {{-- ============================================ --}}
    {{-- NOVOS PRODUTOS --}}
    {{-- ============================================ --}}
    @if(isset($novosProdutos) && $novosProdutos->count() > 0)
    <div class="row mt-4">
        <div class="col-12">
            <h2 class="mb-3">🆕 Novos Produtos</h2>
        </div>
        @foreach($novosProdutos as $produto)
            <div class="col-lg-3 col-md-4 col-6 mb-4">
                <div class="card product-card h-100 shadow-sm">
                    <div class="position-relative">
                        <div class="card-img-top d-flex align-items-center justify-content-center bg-light" style="height: 200px;">
                            @if($produto->imagem)
                                <img src="{{ asset('storage/' . $produto->imagem) }}" 
                                     alt="{{ $produto->descricao }}"
                                     style="height: 100%; width: 100%; object-fit: cover;">
                            @else
                                <i class="bi bi-plug fs-1 text-muted"></i>
                            @endif
                        </div>
                        <span class="badge bg-info position-absolute top-0 start-0 m-2">NOVO</span>
                    </div>
                    <div class="card-body d-flex flex-column">
                        <h6 class="card-title text-truncate">{{ Str::limit($produto->descricao, 35) }}</h6>
                        <p class="card-text mt-auto">
                            @if($produto->preco_promocional && $produto->preco_promocional > 0)
                                <span class="text-decoration-line-through text-muted me-1">
                                    R$ {{ number_format($produto->valor_unitario, 2, ',', '.') }}
                                </span>
                                <span class="fw-bold text-success">
                                    R$ {{ number_format($produto->preco_promocional, 2, ',', '.') }}
                                </span>
                            @else
                                <span class="fw-bold text-success">
                                    R$ {{ number_format($produto->valor_unitario, 2, ',', '.') }}
                                </span>
                            @endif
                        </p>
                        <small class="text-muted mb-2"><i class="bi bi-tag"></i> {{ $produto->categoria ?? 'Geral' }}</small>
                        <a href="{{ route('produtos.show', $produto->slug) }}" class="btn btn-sm btn-outline-success w-100">
                            <i class="bi bi-eye"></i> Ver Detalhes
                        </a>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    {{-- PAGINAÇÃO --}}
    @if($novosProdutos->hasPages())
    <div class="row mt-2">
        <div class="col-12">
            <div class="d-flex justify-content-center">
                {{ $novosProdutos->appends(request()->except(['page_destaque', 'page_ofertas', 'page_novos', 'page_vendidos', 'page_todos']))->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </div>
    @endif
    @endif

    {{-- ============================================ --}}
    {{-- MAIS VENDIDOS --}}
    {{-- ============================================ --}}
    @if(isset($maisVendidos) && $maisVendidos->count() > 0)
    <div class="row mt-4">
        <div class="col-12">
            <h2 class="mb-3">🏆 Mais Vendidos</h2>
        </div>
        @foreach($maisVendidos as $produto)
            <div class="col-lg-3 col-md-4 col-6 mb-4">
                <div class="card product-card h-100 shadow-sm">
                    <div class="position-relative">
                        <div class="card-img-top d-flex align-items-center justify-content-center bg-light" style="height: 200px;">
                            @if($produto->imagem)
                                <img src="{{ asset('storage/' . $produto->imagem) }}" 
                                     alt="{{ $produto->descricao }}"
                                     style="height: 100%; width: 100%; object-fit: cover;">
                            @else
                                <i class="bi bi-plug fs-1 text-muted"></i>
                            @endif
                        </div>
                        <span class="badge bg-warning position-absolute top-0 start-0 m-2">⭐</span>
                    </div>
                    <div class="card-body d-flex flex-column">
                        <h6 class="card-title text-truncate">{{ Str::limit($produto->descricao, 35) }}</h6>
                        <p class="card-text mt-auto">
                            @if($produto->preco_promocional && $produto->preco_promocional > 0)
                                <span class="text-decoration-line-through text-muted me-1">
                                    R$ {{ number_format($produto->valor_unitario, 2, ',', '.') }}
                                </span>
                                <span class="fw-bold text-warning">
                                    R$ {{ number_format($produto->preco_promocional, 2, ',', '.') }}
                                </span>
                            @else
                                <span class="fw-bold text-warning">
                                    R$ {{ number_format($produto->valor_unitario, 2, ',', '.') }}
                                </span>
                            @endif
                        </p>
                        <small class="text-muted mb-2"><i class="bi bi-tag"></i> {{ $produto->categoria ?? 'Geral' }}</small>
                        <a href="{{ route('produtos.show', $produto->slug) }}" class="btn btn-sm btn-outline-warning w-100">
                            <i class="bi bi-eye"></i> Ver Detalhes
                        </a>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    {{-- PAGINAÇÃO --}}
    @if($maisVendidos->hasPages())
    <div class="row mt-2">
        <div class="col-12">
            <div class="d-flex justify-content-center">
                {{ $maisVendidos->appends(request()->except(['page_destaque', 'page_ofertas', 'page_novos', 'page_vendidos', 'page_todos']))->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </div>
    @endif
    @endif

    {{-- ============================================ --}}
    {{-- TODOS OS PRODUTOS DISPONÍVEIS --}}
    {{-- ============================================ --}}
    @if(isset($produtosDisponiveis) && $produtosDisponiveis->count() > 0)
    <div class="row mt-5">
        <div class="col-12">
            <h2 class="mb-3">📦 Todos os Produtos Disponíveis</h2>
            <p class="text-muted">Mostrando {{ $produtosDisponiveis->firstItem() ?? 0 }} a {{ $produtosDisponiveis->lastItem() ?? 0 }} de {{ $produtosDisponiveis->total() }} produtos</p>
        </div>
        @foreach($produtosDisponiveis as $produto)
            <div class="col-lg-3 col-md-4 col-6 mb-4">
                <div class="card product-card h-100 shadow-sm">
                    <div class="position-relative">
                        <div class="card-img-top d-flex align-items-center justify-content-center bg-light" style="height: 200px;">
                            @if($produto->imagem)
                                <img src="{{ asset('storage/' . $produto->imagem) }}" 
                                     alt="{{ $produto->descricao }}"
                                     style="height: 100%; width: 100%; object-fit: cover;">
                            @else
                                <i class="bi bi-plug fs-1 text-muted"></i>
                            @endif
                        </div>
                        @if($produto->preco_promocional && $produto->preco_promocional > 0)
                            <span class="badge bg-danger position-absolute top-0 end-0 m-2">OFERTA</span>
                        @endif
                    </div>
                    <div class="card-body d-flex flex-column">
                        <h6 class="card-title text-truncate">{{ Str::limit($produto->descricao, 35) }}</h6>
                        <p class="card-text mt-auto">
                            @if($produto->preco_promocional && $produto->preco_promocional > 0)
                                <span class="text-decoration-line-through text-muted me-1">
                                    R$ {{ number_format($produto->valor_unitario, 2, ',', '.') }}
                                </span>
                                <span class="fw-bold text-primary">
                                    R$ {{ number_format($produto->preco_promocional, 2, ',', '.') }}
                                </span>
                            @else
                                <span class="fw-bold text-primary">
                                    R$ {{ number_format($produto->valor_unitario, 2, ',', '.') }}
                                </span>
                            @endif
                        </p>
                        <small class="text-muted mb-2"><i class="bi bi-tag"></i> {{ $produto->categoria ?? 'Geral' }}</small>
                        <a href="{{ route('produtos.show', $produto->slug) }}" class="btn btn-sm btn-outline-primary w-100">
                            <i class="bi bi-eye"></i> Ver Detalhes
                        </a>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    {{-- PAGINAÇÃO --}}
    @if($produtosDisponiveis->hasPages())
    <div class="row mt-2">
        <div class="col-12">
            <div class="d-flex justify-content-center">
                {{ $produtosDisponiveis->appends(request()->except(['page_destaque', 'page_ofertas', 'page_novos', 'page_vendidos', 'page_todos']))->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </div>
    @endif
    @else
    <div class="row mt-5">
        <div class="col-12">
            <div class="alert alert-info text-center">
                <i class="bi bi-info-circle fs-3"></i>
                <h4 class="mt-2">Nenhum produto disponível no momento</h4>
                <p>Em breve teremos novos produtos em nosso catálogo!</p>
            </div>
        </div>
    </div>
    @endif
</div>

<style>
    .banner-slide { 
        border-radius: 12px;
        transition: all 0.3s ease;
    }
    
    .product-card { 
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    
    .product-card:hover { 
        transform: translateY(-5px);
        box-shadow: 0 8px 25px rgba(0,0,0,0.15) !important;
    }

    .carousel-control-prev-icon,
    .carousel-control-next-icon {
        background-size: 60% 60%;
        width: 50px;
        height: 50px;
        display: flex;
        align-items: center;
        justify-content: center;
        background-color: rgba(0,0,0,0.5) !important;
        border-radius: 50% !important;
    }

    /* Ícones maiores */
    .product-card .fs-1 {
        font-size: 4rem !important;
    }
    
    .card-img-top .fs-1 {
        font-size: 4rem !important;
    }

    /* Paginação melhorada */
    .pagination {
        gap: 0.25rem;
    }
    
    .pagination .page-link {
        border-radius: 0.375rem;
        padding: 0.5rem 1rem;
        min-width: 2.5rem;
        text-align: center;
    }
    
    .pagination .page-item.active .page-link {
        background-color: #0d6efd;
        border-color: #0d6efd;
    }

    @media (max-width: 768px) {
        .banner-slide {
            padding: 2rem 1rem !important;
            min-height: 200px !important;
        }
        .banner-slide h1,
        .banner-slide h2 {
            font-size: 1.5rem !important;
        }
        .banner-slide .lead {
            font-size: 0.9rem !important;
        }
        .banner-slide .btn {
            font-size: 0.9rem !important;
            padding: 0.5rem 1.5rem !important;
        }
        
        .product-card .fs-1,
        .card-img-top .fs-1 {
            font-size: 2.5rem !important;
        }
        
        .pagination .page-link {
            padding: 0.375rem 0.75rem;
            font-size: 0.875rem;
            min-width: 2rem;
        }
    }
</style>

{{-- Script para animação automática do carrossel --}}
@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const carousel = document.getElementById('bannerCarousel');
        if (carousel) {
            const bsCarousel = new bootstrap.Carousel(carousel, {
                interval: 5000,
                pause: 'hover',
                wrap: true
            });
        }
    });
</script>
@endpush
@endsection