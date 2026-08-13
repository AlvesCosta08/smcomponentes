@extends('layouts.app')

@section('title', 'Gerenciar Produtos - Admin')

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h2 mb-0">📦 Produtos</h1>
            <small class="text-muted">Gerenciamento de produtos da loja</small>
        </div>
        <div>
            <a href="{{ route('admin.produtos.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> Novo Produto
            </a>
            <a href="{{ route('admin.produtos.index') }}" class="btn btn-secondary">
                <i class="bi bi-arrow-clockwise"></i> Atualizar
            </a>
            <a href="{{ route('admin.produtos.export') }}" class="btn btn-success">
                <i class="bi bi-file-excel"></i> Exportar
            </a>
        </div>
    </div>

    <!-- Filtros -->
    <div class="admin-card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.produtos.index') }}" class="row g-3">
                <div class="col-md-3">
                    <label for="search" class="form-label">Buscar</label>
                    <input type="text" name="search" id="search" class="form-control" 
                           placeholder="Descrição, referência..." value="{{ request('search') }}">
                </div>
                <div class="col-md-2">
                    <label for="categoria" class="form-label">Categoria</label>
                    <select name="categoria" id="categoria" class="form-select">
                        <option value="">Todas</option>
                        @foreach($categorias as $categoria)
                            <option value="{{ $categoria }}" {{ request('categoria') == $categoria ? 'selected' : '' }}>
                                {{ $categoria }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="ativo" class="form-label">Status</label>
                    <select name="ativo" id="ativo" class="form-select">
                        <option value="">Todos</option>
                        <option value="1" {{ request('ativo') == '1' ? 'selected' : '' }}>Ativo</option>
                        <option value="0" {{ request('ativo') == '0' ? 'selected' : '' }}>Inativo</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="estoque" class="form-label">Estoque</label>
                    <select name="estoque" id="estoque" class="form-select">
                        <option value="">Todos</option>
                        <option value="disponivel" {{ request('estoque') == 'disponivel' ? 'selected' : '' }}>Disponível</option>
                        <option value="baixo" {{ request('estoque') == 'baixo' ? 'selected' : '' }}>Estoque Baixo</option>
                        <option value="zerado" {{ request('estoque') == 'zerado' ? 'selected' : '' }}>Zerado</option>
                    </select>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-filter"></i> Filtrar
                    </button>
                </div>
                <div class="col-md-1 d-flex align-items-end">
                    <a href="{{ route('admin.produtos.index') }}" class="btn btn-secondary w-100" title="Limpar filtros">
                        <i class="bi bi-eraser"></i>
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Estatísticas -->
    <div class="row g-3 mb-4">
        <div class="col-md-2">
            <div class="admin-stat-card border-primary">
                <div class="stat-number text-primary">{{ $estatisticas['total'] ?? 0 }}</div>
                <div class="stat-label">Total</div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="admin-stat-card border-success">
                <div class="stat-number text-success">{{ $estatisticas['com_estoque'] ?? 0 }}</div>
                <div class="stat-label">Disponíveis</div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="admin-stat-card border-warning">
                <div class="stat-number text-warning">{{ $estatisticas['estoque_baixo'] ?? 0 }}</div>
                <div class="stat-label">Estoque Baixo</div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="admin-stat-card border-danger">
                <div class="stat-number text-danger">{{ $estatisticas['sem_estoque'] ?? 0 }}</div>
                <div class="stat-label">Sem Estoque</div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="admin-stat-card border-info">
                <div class="stat-number text-info">{{ $estatisticas['ativos'] ?? 0 }}</div>
                <div class="stat-label">Ativos</div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="admin-stat-card border-secondary">
                <div class="stat-number text-secondary">{{ $estatisticas['inativos'] ?? 0 }}</div>
                <div class="stat-label">Inativos</div>
            </div>
        </div>
    </div>

    <!-- Lista de Produtos -->
    <div class="admin-card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span><i class="bi bi-list-ul me-2 text-primary"></i>Lista de Produtos</span>
            <span class="badge bg-secondary">{{ $produtos->total() ?? 0 }} produtos</span>
        </div>
        <div class="card-body p-0">
            @if($produtos && $produtos->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover admin-table mb-0">
                        <thead>
                            <tr>
                                <th style="width: 5%;">ID</th>
                                <th style="width: 8%;">Imagem</th>
                                <th style="width: 25%;">Produto</th>
                                <th style="width: 15%;">Categoria</th>
                                <th style="width: 15%;">Preço</th>
                                <th style="width: 10%;">Estoque</th>
                                <th style="width: 12%;">Status</th>
                                <th style="width: 10%;">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($produtos as $produto)
                                <tr>
                                    <td>{{ $produto->id }}</td>
                                    <td>
                                        @if($produto->imagem)
                                            <img src="{{ asset('storage/' . $produto->imagem) }}" 
                                                 alt="{{ $produto->descricao }}"
                                                 style="width: 50px; height: 50px; object-fit: cover; border-radius: 4px;">
                                        @else
                                            <div class="bg-light d-flex align-items-center justify-content-center" 
                                                 style="width: 50px; height: 50px; border-radius: 4px;">
                                                <i class="bi bi-box text-muted" style="font-size: 1.5rem;"></i>
                                            </div>
                                        @endif
                                    </td>
                                    <td>
                                        <strong>{{ Str::limit($produto->descricao, 40) }}</strong>
                                        <br>
                                        <small class="text-muted">Ref: {{ $produto->referencia ?? '-' }}</small>
                                    </td>
                                    <td>{{ Str::limit($produto->categoria, 20) }}</td>
                                    <td>
                                        @if($produto->preco_promocional && $produto->preco_promocional > 0)
                                            <span class="text-decoration-line-through text-muted small">
                                                R$ {{ number_format($produto->valor_unitario, 2, ',', '.') }}
                                            </span>
                                            <br>
                                            <span class="fw-bold text-danger">
                                                R$ {{ number_format($produto->preco_promocional, 2, ',', '.') }}
                                            </span>
                                        @else
                                            <span class="fw-bold">
                                                R$ {{ number_format($produto->valor_unitario, 2, ',', '.') }}
                                            </span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($produto->quantidade <= 0)
                                            <span class="badge bg-danger">0</span>
                                        @elseif($produto->quantidade <= $produto->estoque_minimo)
                                            <span class="badge bg-warning text-dark">
                                                {{ $produto->quantidade }}
                                            </span>
                                        @else
                                            <span class="badge bg-success">{{ $produto->quantidade }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $produto->ativo ? 'success' : 'secondary' }}">
                                            {{ $produto->ativo ? 'Ativo' : 'Inativo' }}
                                        </span>
                                        @if($produto->destaque)
                                            <span class="badge bg-warning text-dark">⭐</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm" role="group">
                                            <a href="{{ route('admin.produtos.show', $produto->id) }}" 
                                               class="btn btn-outline-info" title="Ver">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <a href="{{ route('admin.produtos.edit', $produto->id) }}" 
                                               class="btn btn-outline-warning" title="Editar">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <form action="{{ route('admin.produtos.destroy', $produto->id) }}" 
                                                  method="POST" class="d-inline" 
                                                  onsubmit="return confirm('Tem certeza que deseja excluir este produto?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-outline-danger" title="Excluir">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                
                <!-- Paginação Corrigida -->
                <div class="p-3">
                    <div class="d-flex justify-content-between align-items-center flex-wrap">
                        <div class="text-muted small">
                            Mostrando <strong>{{ $produtos->firstItem() ?? 0 }}</strong> 
                            a <strong>{{ $produtos->lastItem() ?? 0 }}</strong> 
                            de <strong>{{ $produtos->total() }}</strong> produtos
                        </div>
                        <div>
                            {{ $produtos->appends(request()->query())->links() }}
                        </div>
                    </div>
                </div>
            @else
                <div class="text-center py-5">
                    <i class="bi bi-inbox fs-1 d-block mb-3 text-muted"></i>
                    <p class="text-muted fs-5">Nenhum produto encontrado.</p>
                    <a href="{{ route('admin.produtos.create') }}" class="btn btn-primary">
                        <i class="bi bi-plus-circle"></i> Adicionar primeiro produto
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>

<style>
    .admin-stat-card {
        background: #fff;
        border-radius: 12px;
        padding: 15px 20px;
        border: 1px solid #e9ecef;
        transition: all 0.2s ease;
        text-align: center;
    }
    .admin-stat-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    }
    .stat-number {
        font-size: 2rem;
        font-weight: 700;
    }
    .stat-label {
        font-size: 0.85rem;
        color: #6c757d;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .admin-card {
        background: #fff;
        border-radius: 12px;
        border: 1px solid #e9ecef;
        overflow: hidden;
    }
    .admin-card .card-header {
        background: #f8f9fa;
        padding: 12px 20px;
        border-bottom: 1px solid #e9ecef;
        font-weight: 600;
    }
    .admin-card .card-body {
        padding: 20px;
    }
    .admin-table th {
        background: #f8f9fa;
        font-weight: 600;
        font-size: 0.85rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #6c757d;
        border-bottom: 2px solid #dee2e6;
    }
    .admin-table td {
        vertical-align: middle;
        padding: 10px 12px;
    }
    .admin-table tr:hover {
        background-color: #f8f9fa;
    }
    
    /* Estilos para a paginação */
    .pagination {
        margin-bottom: 0;
    }
    .pagination .page-link {
        color: #0d6efd;
        border-radius: 4px;
        margin: 0 2px;
    }
    .pagination .page-item.active .page-link {
        background-color: #0d6efd;
        border-color: #0d6efd;
        color: #fff;
    }
    .pagination .page-item.disabled .page-link {
        color: #6c757d;
    }
    .pagination .page-link:hover {
        background-color: #e9ecef;
        border-color: #dee2e6;
    }
    
    /* Responsivo */
    @media (max-width: 768px) {
        .admin-table {
            font-size: 0.85rem;
        }
        .admin-table th,
        .admin-table td {
            padding: 6px 8px;
        }
        .stat-number {
            font-size: 1.5rem;
        }
        .admin-stat-card {
            padding: 10px 15px;
        }
    }
</style>

@push('scripts')
<script>
    // Auto-submit do formulário ao mudar os selects
    document.addEventListener('DOMContentLoaded', function() {
        const selects = document.querySelectorAll('#categoria, #ativo, #estoque');
        selects.forEach(select => {
            select.addEventListener('change', function() {
                this.closest('form').submit();
            });
        });
    });
</script>
@endpush
@endsection