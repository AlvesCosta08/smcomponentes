@props(['banners' => []])

@if($banners->count() > 0)
<div class="row mb-4">
    <div class="col-12">
        <div id="bannerCarousel" class="carousel slide" data-bs-ride="carousel">
            <!-- Indicadores -->
            <div class="carousel-indicators">
                @foreach($banners as $index => $banner)
                <button type="button" 
                        data-bs-target="#bannerCarousel" 
                        data-bs-slide-to="{{ $index }}" 
                        class="{{ $loop->first ? 'active' : '' }}"
                        aria-label="Slide {{ $index + 1 }}"></button>
                @endforeach
            </div>

            <!-- Slides -->
            <div class="carousel-inner rounded-4 shadow">
                @foreach($banners as $banner)
                <div class="carousel-item {{ $loop->first ? 'active' : '' }}">
                    <div class="banner-slide" 
                         style="
                            {{ $banner->estilo_fundo }}
                            padding: 3rem 2rem;
                            min-height: 300px;
                            position: relative;
                            overflow: hidden;
                         ">
                        
                        <!-- Imagem de Fundo -->
                        @if($banner->imagem_url && $banner->tipo != 'texto')
                        <div style="
                            position: absolute;
                            top: 0;
                            left: 0;
                            width: 100%;
                            height: 100%;
                            background-image: url('{{ $banner->imagem_url }}');
                            background-size: cover;
                            background-position: center;
                            opacity: 0.8;
                            z-index: 0;
                        "></div>
                        @endif

                        <!-- Conteúdo -->
                        <div class="row align-items-center position-relative" style="z-index: 1;">
                            <div class="{{ $banner->tipo == 'imagem' ? 'col-md-8' : 'col-12' }} text-{{ $banner->tipo == 'imagem' ? 'white' : '' }}" 
                                 style="color: {{ $banner->cor_texto ?? '#ffffff' }};">
                                
                                @if($banner->titulo)
                                <h1 class="display-5 fw-bold">{{ $banner->titulo }}</h1>
                                @endif
                                
                                @if($banner->subtitulo)
                                <h3 class="fw-semibold">{{ $banner->subtitulo }}</h3>
                                @endif
                                
                                @if($banner->descricao)
                                <p class="lead mb-0">{{ $banner->descricao }}</p>
                                @endif
                            </div>

                            <!-- Botão -->
                            @if($banner->link && $banner->texto_botao)
                            <div class="col-md-4 text-md-end mt-3 mt-md-0">
                                <a href="{{ $banner->link }}" 
                                   class="btn btn-{{ $banner->cor_botao ?? 'light' }} btn-lg px-4 rounded-pill shadow fw-bold"
                                   @if(Str::startsWith($banner->link, 'http')) target="_blank" @endif>
                                    <i class="bi bi-arrow-right"></i> {{ $banner->texto_botao }}
                                </a>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
                @endforeach
            </div>

            <!-- Controles -->
            <button class="carousel-control-prev" type="button" data-bs-target="#bannerCarousel" data-bs-slide="prev">
                <span class="carousel-control-prev-icon bg-dark rounded-circle p-2" aria-hidden="true"></span>
                <span class="visually-hidden">Anterior</span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#bannerCarousel" data-bs-slide="next">
                <span class="carousel-control-next-icon bg-dark rounded-circle p-2" aria-hidden="true"></span>
                <span class="visually-hidden">Próximo</span>
            </button>
        </div>
    </div>
</div>

<style>
    .banner-slide {
        transition: all 0.5s ease;
        border-radius: 12px;
        min-height: 250px;
    }
    
    @media (max-width: 768px) {
        .banner-slide {
            min-height: 200px;
            padding: 2rem 1rem !important;
        }
        .banner-slide h1 {
            font-size: 1.5rem !important;
        }
        .banner-slide .lead {
            font-size: 0.9rem !important;
        }
    }
</style>
@endif