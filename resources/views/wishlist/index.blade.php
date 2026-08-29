{{-- resources/views/wishlist/index.blade.php --}}
<x-app-layout>
    <div class="container py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3 mb-0">❤️ Minhas Listas de Desejos</h1>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createWishlistModal">
                <i class="fas fa-plus me-1"></i> Nova Lista
            </button>
        </div>

        <!-- Alertas -->
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i> {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <!-- Listas do Usuário -->
        <div class="row g-4">
            @forelse($wishlists as $wishlist)
                <div class="col-md-6 col-lg-4">
                    <div class="card h-100 shadow-sm hover-shadow transition border-0">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <h5 class="card-title mb-0">
                                    {{ $wishlist->nome }}
                                    @if($wishlist->is_default)
                                        <span class="badge bg-primary">Padrão</span>
                                    @endif
                                    @if($wishlist->is_public)
                                        <span class="badge bg-info">Pública</span>
                                    @endif
                                </h5>
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-link text-dark p-0" data-bs-toggle="dropdown">
                                        <i class="fas fa-ellipsis-v"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        <li>
                                            {{-- ✅ CORRIGIDO: Usar route('cliente.wishlist.show') --}}
                                            <a class="dropdown-item" href="{{ route('cliente.wishlist.show', $wishlist->id) }}">
                                                <i class="fas fa-eye me-2"></i> Ver
                                            </a>
                                        </li>
                                        <li>
                                            <button class="dropdown-item" data-bs-toggle="modal" 
                                                    data-bs-target="#editWishlistModal{{ $wishlist->id }}">
                                                <i class="fas fa-edit me-2"></i> Editar
                                            </button>
                                        </li>
                                        @if(!$wishlist->is_default)
                                            <li>
                                                <hr class="dropdown-divider">
                                            </li>
                                            <li>
                                                {{-- ✅ CORRIGIDO: Usar route('cliente.wishlist.destroy') --}}
                                                <form action="{{ route('cliente.wishlist.destroy', $wishlist->id) }}" method="POST" 
                                                      onsubmit="return confirm('Tem certeza que deseja excluir esta lista?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="dropdown-item text-danger">
                                                        <i class="fas fa-trash me-2"></i> Excluir
                                                    </button>
                                                </form>
                                            </li>
                                        @endif
                                    </ul>
                                </div>
                            </div>
                            
                            <p class="card-text text-muted small mb-3">
                                {{ $wishlist->descricao ?? 'Sem descrição' }}
                            </p>
                            
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="badge bg-secondary">
                                    <i class="fas fa-box me-1"></i> {{ $wishlist->countItems() }} produtos
                                </span>
                                {{-- ✅ CORRIGIDO: Usar route('cliente.wishlist.show') --}}
                                <a href="{{ route('cliente.wishlist.show', $wishlist->id) }}" class="btn btn-sm btn-outline-primary">
                                    Ver <i class="fas fa-arrow-right ms-1"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Modal Editar -->
                @include('wishlist.partials.edit-modal', ['wishlist' => $wishlist])

            @empty
                <div class="col-12">
                    <div class="text-center py-5">
                        <i class="fas fa-heart fa-4x text-muted mb-3"></i>
                        <h5 class="text-muted">Você ainda não tem listas de desejos</h5>
                        <p class="text-muted">Crie sua primeira lista para começar a salvar seus produtos favoritos!</p>
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createWishlistModal">
                            <i class="fas fa-plus me-1"></i> Criar Lista
                        </button>
                    </div>
                </div>
            @endforelse
        </div>

        <!-- Modal Criar -->
        @include('wishlist.partials.create-modal')
    </div>

    @push('styles')
    <style>
        .hover-shadow {
            transition: all 0.3s ease;
        }
        .hover-shadow:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.1) !important;
        }
        .transition {
            transition: all 0.3s ease;
        }
    </style>
    @endpush
</x-app-layout>