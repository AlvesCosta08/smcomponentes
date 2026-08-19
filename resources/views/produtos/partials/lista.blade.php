@if($produtos->isNotEmpty())
    <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-4" id="products-grid">
        @foreach($produtos as $produto)
            <div class="col product-item">
                <div class="card h-100 shadow-sm">
                    <div class="position-relative">
                        <img src="{{ $produto->imagem_url ?? asset('images/produto-placeholder.jpg') }}" 
                             class="card-img-top" 
                             alt="{{ $produto->descricao }}"
                             style="height: 200px; object-fit: cover;">
                        
                        @if($produto->tem_promocao ?? false)
                            @php
                                $desconto = $produto->valor_unitario > 0 
                                    ? round((1 - ($produto->preco_promocional / $produto->valor_unitario)) * 100) 
                                    : 0;
                            @endphp
                            <span class="badge bg-danger position-absolute top-0 end-0 m-2">
                                -{{ $desconto }}%
                            </span>
                        @endif
                    </div>
                    
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title">{{ $produto->descricao }}</h5>
                        
                        @if(isset($produto->categoria) && is_object($produto->categoria))
                            <small class="text-muted">{{ $produto->categoria->nome ?? 'Sem categoria' }}</small>
                        @else
                            <small class="text-muted">Sem categoria</small>
                        @endif
                        
                        <div class="mt-2">
                            @if($produto->tem_promocao ?? false)
                                <span class="text-decoration-line-through text-muted me-2">
                                    R$ {{ number_format($produto->valor_unitario ?? 0, 2, ',', '.') }}
                                </span>
                                <span class="text-danger fw-bold">
                                    R$ {{ number_format($produto->preco_promocional ?? 0, 2, ',', '.') }}
                                </span>
                            @else
                                <span class="fw-bold">
                                    R$ {{ number_format($produto->valor_unitario ?? 0, 2, ',', '.') }}
                                </span>
                            @endif
                        </div>
                        
                        <div class="mt-2">
                            @if(isset($produto->quantidade) && $produto->quantidade > 0)
                                <span class="badge bg-success">Em estoque</span>
                            @else
                                <span class="badge bg-danger">Indisponível</span>
                            @endif
                        </div>
                    </div>
                    
                    <div class="card-footer bg-transparent border-0">
                        <a href="{{ route('produtos.show', $produto->slug ?? $produto->id) }}" 
                           class="btn btn-outline-primary w-100">
                            Ver detalhes
                        </a>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
    
    @if($produtos->hasPages())
        <div class="row mt-4">
            <div class="col-12">
                {{ $produtos->appends(['q' => $termo ?? ''])->links() }}
            </div>
        </div>
    @endif
@else
    <div class="row">
        <div class="col-12 text-center py-5">
            <i class="bi bi-search display-1 text-muted"></i>
            <h3 class="mt-3">Nenhum produto encontrado</h3>
            <p class="text-muted">Tente buscar por outro termo.</p>
        </div>
    </div>
@endif