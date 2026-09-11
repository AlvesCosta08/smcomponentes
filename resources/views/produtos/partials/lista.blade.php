{{-- resources/views/produtos/partials/lista.blade.php --}}
<div class="row g-4" id="products-grid">
    @forelse($produtos as $produto)
        <div class="col-xl-3 col-lg-4 col-md-6 col-6 product-item">
            <div class="card product-card h-100">
                <div class="position-relative">
                    @if($produto->imagem)
                        @php
                            $filename = basename($produto->imagem);
                        @endphp
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
                    
                    <!-- Badge de Disponibilidade -->
                    @if($produto->pode_comprar)
                        <span class="badge bg-success position-absolute top-0 end-0 m-2 px-3 py-1 rounded-pill">
                            <i class="bi bi-check-circle"></i> Disponível
                        </span>
                    @else
                        <span class="badge bg-danger position-absolute top-0 end-0 m-2 px-3 py-1 rounded-pill">
                            <i class="bi bi-x-circle"></i> Indisponível
                        </span>
                    @endif

                    @if($produto->tem_promocao)
                        <span class="badge bg-danger position-absolute top-0 start-0 m-2 px-3 py-1 rounded-pill">
                            -{{ $produto->desconto_percentual }}%
                        </span>
                    @endif
                </div>
                
                <div class="card-body d-flex flex-column">
                    <h6 class="card-title text-truncate" title="{{ $produto->descricao }}">
                        {{ Str::limit($produto->descricao, 40) }}
                    </h6>
                    
                    <div class="mt-auto">
                        <span class="categoria-badge">
                            <i class="bi bi-tag"></i> 
                            {{ $produto->categoria?->nome ?? 'Sem categoria' }}
                        </span>
                        
                        <p class="card-text mt-2 mb-0">
                            @if($produto->tem_promocao)
                                <span class="text-decoration-line-through text-muted small me-1">
                                    {{ $produto->preco_atacado_formatado }}
                                </span>
                                <span class="price text-danger">
                                    {{ $produto->preco_promocional_formatado }}
                                </span>
                            @else
                                <span class="price">{{ $produto->preco_atacado_formatado }}</span>
                            @endif
                        </p>
                        
                        <a href="{{ route('produtos.show', $produto->slug) }}" 
                           class="btn btn-outline-primary w-100 mt-2 rounded-pill">
                            <i class="bi bi-eye"></i> Detalhes
                        </a>
                    </div>
                </div>
            </div>
        </div>
    @empty
        <div class="col-12 text-center py-5">
            <i class="bi bi-search display-4 d-block mb-3 text-muted"></i>
            <p class="text-muted fs-5">Nenhum produto encontrado.</p>
        </div>
    @endforelse
</div>

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
    .categoria-badge {
        font-size: 0.75rem;
        color: #6c757d;
        background: #f8f9fa;
        padding: 2px 10px;
        border-radius: 20px;
        border: 1px solid #e9ecef;
    }
    .price {
        font-size: 1.1rem;
        font-weight: 700;
        color: #0d6efd;
    }
</style>