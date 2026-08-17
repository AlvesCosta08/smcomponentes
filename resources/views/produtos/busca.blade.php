@extends('layouts.app')

@section('title', 'Busca de Produtos')

@section('content')
<div class="container py-4">
    <div class="row">
        <div class="col-12">
            <h1 class="h2 mb-3">Busca de Produtos</h1>
            
            <!-- Barra de busca com autocomplete -->
            <div class="mb-4">
                <div class="position-relative">
                    <input type="text" 
                           id="search-input"
                           class="form-control form-control-lg" 
                           placeholder="Digite para buscar produtos..." 
                           value="{{ $termo ?? '' }}"
                           autocomplete="off">
                    <div id="search-loading" class="position-absolute top-50 end-0 translate-middle-y me-3 d-none">
                        <div class="spinner-border spinner-border-sm text-primary" role="status">
                            <span class="visually-hidden">Carregando...</span>
                        </div>
                    </div>
                </div>
                <small class="text-muted" id="result-count">
                    @if(isset($produtos))
                        {{ $produtos->total() }} produto(s) encontrado(s)
                    @endif
                </small>
            </div>
        </div>
    </div>

    <!-- Resultados -->
    <div id="search-results">
        @if(isset($produtos) && $produtos->isNotEmpty())
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
                                
                                @if(isset($produto->categoria))
                                    @if(is_object($produto->categoria))
                                        <small class="text-muted">{{ $produto->categoria->nome ?? 'Sem categoria' }}</small>
                                    @elseif(is_string($produto->categoria))
                                        <small class="text-muted">{{ $produto->categoria }}</small>
                                    @else
                                        <small class="text-muted">Sem categoria</small>
                                    @endif
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
            
            <div class="row mt-4">
                <div class="col-12">
                    {{ $produtos->appends(['q' => $termo ?? ''])->links() }}
                </div>
            </div>
        @elseif(isset($produtos) && $produtos->isEmpty())
            <div class="row">
                <div class="col-12 text-center py-5">
                    <i class="bi bi-search display-1 text-muted"></i>
                    <h3 class="mt-3">Nenhum produto encontrado</h3>
                    <p class="text-muted">Tente buscar por outro termo.</p>
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
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('search-input');
    const resultsContainer = document.getElementById('search-results');
    const loadingIndicator = document.getElementById('search-loading');
    const resultCount = document.getElementById('result-count');
    let searchTimeout = null;
    let currentTerm = '{{ $termo ?? '' }}';

    // Função para realizar a busca
    function performSearch(term) {
        if (term.length < 1) {
            // Se o termo estiver vazio, recarrega a página para mostrar todos os produtos
            window.location.href = '{{ route("produtos.index") }}';
            return;
        }

        // Mostrar loading
        loadingIndicator.classList.remove('d-none');
        resultCount.textContent = 'Buscando...';

        // Fazer a requisição AJAX
        fetch(`{{ route('produtos.buscar') }}?q=${encodeURIComponent(term)}`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Erro na resposta do servidor');
            }
            return response.json();
        })
        .then(data => {
            // Atualizar resultados
            resultsContainer.innerHTML = data.html;
            
            // Atualizar contagem
            if (data.total > 0) {
                resultCount.textContent = `${data.total} produto(s) encontrado(s)`;
            } else {
                resultCount.textContent = 'Nenhum produto encontrado';
            }
            
            // Esconder loading
            loadingIndicator.classList.add('d-none');
            
            // Atualizar URL sem recarregar a página
            if (term) {
                const url = new URL(window.location);
                url.searchParams.set('q', term);
                window.history.pushState({}, '', url);
            }
        })
        .catch(error => {
            console.error('Erro na busca:', error);
            resultCount.textContent = 'Erro ao buscar produtos';
            loadingIndicator.classList.add('d-none');
            
            // Mostrar mensagem de erro amigável
            resultsContainer.innerHTML = `
                <div class="row">
                    <div class="col-12 text-center py-5">
                        <i class="bi bi-exclamation-triangle display-1 text-warning"></i>
                        <h3 class="mt-3">Ops! Algo deu errado</h3>
                        <p class="text-muted">Não foi possível realizar a busca. Tente novamente.</p>
                    </div>
                </div>
            `;
        });
    }

    // Evento de input com debounce
    searchInput.addEventListener('input', function() {
        const term = this.value.trim();
        
        // Limpar timeout anterior
        if (searchTimeout) {
            clearTimeout(searchTimeout);
        }

        // Se o termo não mudou, não faz nada
        if (term === currentTerm) {
            return;
        }

        currentTerm = term;

        // Se o termo tem menos de 1 caractere, recarrega a página
        if (term.length < 1) {
            window.location.href = '{{ route("produtos.index") }}';
            return;
        }

        // Aguardar 500ms para fazer a busca
        searchTimeout = setTimeout(function() {
            performSearch(term);
        }, 500);
    });

    // Buscar ao pressionar Enter
    searchInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            const term = this.value.trim();
            if (term.length > 0) {
                performSearch(term);
            }
        }
    });

    // Atualizar ao carregar a página com termo
    if (currentTerm) {
        resultCount.textContent = 'Carregando...';
        performSearch(currentTerm);
    }
});
</script>
@endpush
@endsection