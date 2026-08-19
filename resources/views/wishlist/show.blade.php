{{-- resources/views/wishlist/show.blade.php --}}
<x-app-layout>
    <div class="container py-4">
        <!-- Cabeçalho -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <a href="{{ route('wishlist.index') }}" class="text-muted text-decoration-none mb-2 d-inline-block">
                    <i class="fas fa-arrow-left me-1"></i> Voltar
                </a>
                <h1 class="h3 mb-0">
                    {{ $wishlist->nome }}
                    @if($wishlist->is_default)
                        <span class="badge bg-primary ms-2">Padrão</span>
                    @endif
                    @if($wishlist->is_public)
                        <span class="badge bg-info ms-2">Pública</span>
                    @endif
                </h1>
                @if($wishlist->descricao)
                    <p class="text-muted mt-1">{{ $wishlist->descricao }}</p>
                @endif
            </div>
            <div>
                <span class="badge bg-secondary fs-6">
                    <i class="fas fa-box me-1"></i> {{ $wishlist->countItems() }} produtos
                </span>
            </div>
        </div>

        <!-- Produtos -->
        @if($wishlist->items->count() > 0)
            <div class="row g-4">
                @foreach($wishlist->items as $item)
                    <div class="col-md-4 col-lg-3">
                        <div class="card h-100 shadow-sm product-card">
                            <!-- Imagem -->
                            <div class="position-relative">
                                <img src="{{ $item->produto->imagem_url ?? asset('images/produto-placeholder.jpg') }}" 
                                     class="card-img-top" 
                                     alt="{{ $item->produto->descricao }}"
                                     style="height: 200px; object-fit: cover;">
                                
                                <!-- Badge Status -->
                                @if(!$item->produto->isDisponivel())
                                    <span class="badge bg-danger position-absolute top-0 end-0 m-2">
                                        Indisponível
                                    </span>
                                @endif

                                <!-- Botão Remover -->
                                <button type="button" 
                                        class="btn btn-sm btn-danger position-absolute top-0 start-0 m-2 remove-wishlist-btn"
                                        data-produto-id="{{ $item->produto->id }}"
                                        data-wishlist-id="{{ $wishlist->id }}"
                                        title="Remover da lista">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>

                            <div class="card-body">
                                <h6 class="card-title text-truncate">
                                    <a href="{{ route('produtos.show', $item->produto->slug) }}" class="text-decoration-none text-dark">
                                        {{ $item->produto->descricao }}
                                    </a>
                                </h6>
                                <p class="card-text text-muted small">{{ $item->produto->categoria }}</p>
                                
                                <!-- Preço -->
                                <div class="mt-2">
                                    @if($item->produto->tem_promocao)
                                        <span class="text-decoration-line-through text-muted small">
                                            R$ {{ number_format($item->produto->valor_unitario, 2, ',', '.') }}
                                        </span>
                                        <span class="text-success fw-bold">
                                            R$ {{ number_format($item->produto->preco_promocional, 2, ',', '.') }}
                                        </span>
                                    @else
                                        <span class="fw-bold">
                                            R$ {{ number_format($item->produto->valor_unitario, 2, ',', '.') }}
                                        </span>
                                    @endif
                                </div>

                                <!-- Observação -->
                                @if($item->observacao)
                                    <p class="text-muted small mt-2">
                                        <i class="fas fa-pencil-alt me-1"></i> {{ $item->observacao }}
                                    </p>
                                @endif

                                <!-- Ações -->
                                <div class="d-flex gap-2 mt-3">
                                    <a href="{{ route('produtos.show', $item->produto->slug) }}" 
                                       class="btn btn-sm btn-outline-primary flex-grow-1">
                                        <i class="fas fa-eye me-1"></i> Ver
                                    </a>
                                    <form action="{{ route('carrinho.adicionar') }}" method="POST" class="flex-grow-1">
                                        @csrf
                                        <input type="hidden" name="produto_id" value="{{ $item->produto->id }}">
                                        <input type="hidden" name="quantidade" value="1">
                                        <button type="submit" class="btn btn-sm btn-success w-100" 
                                                {{ !$item->produto->isDisponivel() ? 'disabled' : '' }}>
                                            <i class="fas fa-cart-plus me-1"></i> Carrinho
                                        </button>
                                    </form>
                                </div>

                                <!-- Mover para outra lista -->
                                <div class="mt-2">
                                    <select class="form-select form-select-sm move-wishlist-select" 
                                            data-produto-id="{{ $item->produto->id }}"
                                            data-origem-id="{{ $wishlist->id }}">
                                        <option value="">Mover para...</option>
                                        @foreach(auth()->user()->wishlists()->where('id', '!=', $wishlist->id)->get() as $lista)
                                            <option value="{{ $lista->id }}">{{ $lista->nome }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="text-center py-5">
                <i class="fas fa-heart fa-4x text-muted mb-3"></i>
                <h5 class="text-muted">Esta lista está vazia</h5>
                <p class="text-muted">Explore os produtos e adicione seus favoritos!</p>
                <a href="{{ route('produtos.index') }}" class="btn btn-primary">
                    <i class="fas fa-search me-1"></i> Explorar Produtos
                </a>
            </div>
        @endif
    </div>

    @push('styles')
    <style>
        .product-card {
            transition: all 0.3s ease;
            border: 1px solid #eee;
        }
        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.1) !important;
        }
        .remove-wishlist-btn {
            opacity: 0.8;
            transition: all 0.3s ease;
        }
        .remove-wishlist-btn:hover {
            opacity: 1;
            transform: scale(1.1);
        }
    </style>
    @endpush

    @push('scripts')
    <script>
        // Remover produto da wishlist
        document.querySelectorAll('.remove-wishlist-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const produtoId = this.dataset.produtoId;
                const wishlistId = this.dataset.wishlistId;
                
                if (confirm('Remover este produto da lista de desejos?')) {
                    fetch('{{ route("wishlist.remover") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            produto_id: produtoId,
                            wishlist_id: wishlistId
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            location.reload();
                        } else {
                            alert(data.message);
                        }
                    })
                    .catch(error => {
                        alert('Erro ao remover produto');
                    });
                }
            });
        });

        // Mover produto para outra lista
        document.querySelectorAll('.move-wishlist-select').forEach(select => {
            select.addEventListener('change', function() {
                const destinoId = this.value;
                if (!destinoId) return;
                
                const produtoId = this.dataset.produtoId;
                const origemId = this.dataset.origemId;
                
                fetch('{{ route("wishlist.mover") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        produto_id: produtoId,
                        origem_id: origemId,
                        destino_id: destinoId
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert(data.message);
                        this.value = '';
                    }
                })
                .catch(error => {
                    alert('Erro ao mover produto');
                    this.value = '';
                });
            });
        });
    </script>
    @endpush
</x-app-layout>