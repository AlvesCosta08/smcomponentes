@extends('layouts.app')

@section('title', 'Gerenciar Banners - Admin')

@section('content')
<div class="container-fluid px-4 py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h2 mb-0">📸 Banners</h1>
            <small class="text-muted">Gerencie os banners do carrossel</small>
        </div>
        <a href="{{ route('admin.banners.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Novo Banner
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span><i class="bi bi-list-ul me-2"></i>Lista de Banners</span>
            <span class="badge bg-secondary">{{ $banners->count() }} banners</span>
        </div>
        <div class="card-body p-0">
            @if($banners->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover admin-table mb-0">
                        <thead>
                            <tr>
                                <th>Ordem</th>
                                <th>Imagem</th>
                                <th>Título</th>
                                <th>Tipo</th>
                                <th>Status</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($banners as $banner)
                                <tr>
                                    <td>
                                        <i class="bi bi-grip-vertical text-muted me-2"></i>
                                        {{ $banner->ordem }}
                                    </td>
                                    <td>
                                        @if($banner->imagem)
                                            <img src="{{ asset('storage/' . $banner->imagem) }}" 
                                                 alt="{{ $banner->titulo }}"
                                                 style="width: 80px; height: 50px; object-fit: cover; border-radius: 4px;">
                                        @else
                                            <span class="badge bg-secondary">Sem imagem</span>
                                        @endif
                                    </td>
                                    <td>
                                        <strong>{{ $banner->titulo ?? 'Sem título' }}</strong>
                                        @if($banner->subtitulo)
                                            <br>
                                            <small class="text-muted">{{ $banner->subtitulo }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $banner->tipo == 'imagem' ? 'primary' : ($banner->tipo == 'texto' ? 'success' : 'info') }}">
                                            {{ ucfirst($banner->tipo) }}
                                        </span>
                                    </td>
                                    <td>
                                        <form action="{{ route('admin.banners.toggle', $banner) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-{{ $banner->ativo ? 'success' : 'secondary' }}">
                                                {{ $banner->ativo ? 'Ativo' : 'Inativo' }}
                                            </button>
                                        </form>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="{{ route('admin.banners.edit', $banner) }}" class="btn btn-outline-warning">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <form action="{{ route('admin.banners.destroy', $banner) }}" method="POST" 
                                                  onsubmit="return confirm('Tem certeza que deseja excluir este banner?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-outline-danger">
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
            @else
                <div class="text-center py-5">
                    <i class="bi bi-images fs-1 d-block mb-3 text-muted"></i>
                    <p class="text-muted fs-5">Nenhum banner cadastrado.</p>
                    <a href="{{ route('admin.banners.create') }}" class="btn btn-primary">
                        <i class="bi bi-plus-circle"></i> Criar primeiro banner
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
