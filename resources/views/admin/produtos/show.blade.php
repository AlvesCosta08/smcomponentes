@extends('layouts.app')  {{-- ALTERADO --}}

@section('title', 'Detalhes do Produto - Admin')

@section('content')
<div class="container-fluid px-4">
    <div class="page-header d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0"><i class="bi bi-box"></i> Detalhes do Produto</h1>
            <small class="text-muted">#{{ $produto->id }} - {{ $produto->descricao }}</small>
        </div>
        <div>
            <a href="{{ route('admin.produtos.edit', $produto->id) }}" class="btn btn-warning">
                <i class="bi bi-pencil"></i> Editar
            </a>
            <a href="{{ route('admin.produtos.index') }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Voltar
            </a>
        </div>
    </div>

    <div class="row g-4">
        <!-- Informações do Produto -->
        <div class="col-md-8">
            <div class="admin-card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Informações do Produto</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>ID:</strong> #{{ $produto->id }}</p>
                            <p><strong>Descrição:</strong> {{ $produto->descricao }}</p>
                            <p><strong>Referência:</strong> {{ $produto->referencia ?? '-' }}</p>
                            <p><strong>Categoria:</strong> {{ $produto->categoria }}</p>
                            <p><strong>Slug:</strong> <code>{{ $produto->slug }}</code></p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Preço Unitário:</strong> {{ $produto->preco_formatado }}</p>
                            <p><strong>Preço Promocional:</strong> {{ $produto->preco_promocional_formatado ?? '-' }}</p>
                            <p><strong>Tem Promoção:</strong> {{ $produto->tem_promocao ? 'Sim' : 'Não' }}</p>
                            <p><strong>Quantidade:</strong> {{ $produto->quantidade }}</p>
                            <p><strong>Disponibilidade:</strong> {{ $produto->disponibilidade }}</p>
                            <p><strong>Status:</strong> {{ $produto->ativo ? 'Ativo' : 'Inativo' }}</p>
                            <p><strong>Visualizações:</strong> {{ $produto->visualizacoes ?? 0 }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Estoque -->
            <div class="admin-card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0"><i class="bi bi-box-seam"></i> Gerenciar Estoque</h5>
                    <span class="badge bg-primary">Atual: {{ $produto->quantidade }}</span>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.produtos.ajustar-estoque', $produto->id) }}" method="POST" class="row g-3">
                        @csrf
                        <div class="col-md-4">
                            <select name="tipo" class="form-select" required>
                                <option value="adicionar">Adicionar</option>
                                <option value="remover">Remover</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <input type="number" name="quantidade" class="form-control" placeholder="Quantidade" min="1" required>
                        </div>
                        <div class="col-md-4">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="bi bi-arrow-clockwise"></i> Ajustar Estoque
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Imagem -->
        <div class="col-md-4">
            <div class="admin-card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Imagem</h5>
                </div>
                <div class="card-body text-center">
                    <img src="{{ $produto->imagem_url }}" alt="{{ $produto->descricao }}" class="img-fluid rounded" style="max-height: 300px;">
                    @if($produto->imagem)
                        <p class="text-muted mt-2"><small>{{ basename($produto->imagem) }}</small></p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection