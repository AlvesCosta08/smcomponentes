@extends('layouts.app')

@section('title', 'Detalhes do Usuário - Admin')

@section('content')
<div class="container-fluid px-4">
    <div class="page-header d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0"><i class="bi bi-person-circle"></i> Detalhes do Usuário</h1>
            <small class="text-muted">#{{ $usuario->id }} - {{ $usuario->name }}</small>
        </div>
        <div>
            <a href="{{ route('admin.usuarios.edit', $usuario) }}" class="btn btn-warning">
                <i class="bi bi-pencil"></i> Editar
            </a>
            <a href="{{ route('admin.usuarios.index') }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Voltar
            </a>
        </div>
    </div>

    <div class="row g-4">
        <!-- Informações do Usuário -->
        <div class="col-md-8">
            <div class="admin-card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-person"></i> Informações Pessoais</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>ID:</strong> #{{ $usuario->id }}</p>
                            <p><strong>Nome:</strong> {{ $usuario->name }}</p>
                            <p><strong>Email:</strong> {{ $usuario->email }}</p>
                            <p><strong>Telefone:</strong> {{ $usuario->telefone ?? '-' }}</p>
                            <p><strong>Celular:</strong> {{ $usuario->celular ?? '-' }}</p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>CPF:</strong> {{ $usuario->cpf ?? '-' }}</p>
                            <p><strong>Data Nascimento:</strong> {{ $usuario->data_nascimento?->format('d/m/Y') ?? '-' }}</p>
                            <p><strong>Role:</strong>
                                @php
                                    $roleColors = ['Admin' => 'danger', 'Funcionario' => 'warning', 'Cliente' => 'info'];
                                    $roleName = $usuario->roles->first()->name ?? 'Sem Role';
                                @endphp
                                <span class="badge bg-{{ $roleColors[$roleName] ?? 'secondary' }}">
                                    {{ $roleName }}
                                </span>
                            </p>
                            <p><strong>Status:</strong>
                                @if($usuario->ativo)
                                    <span class="badge bg-success">Ativo</span>
                                @else
                                    <span class="badge bg-danger">Inativo</span>
                                @endif
                            </p>
                            <p><strong>Cadastro:</strong> {{ $usuario->created_at->format('d/m/Y H:i') }}</p>
                            <p><strong>Último Acesso:</strong> {{ $usuario->ultimo_acesso?->format('d/m/Y H:i') ?? 'Nunca' }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Endereço -->
            <div class="admin-card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-geo-alt"></i> Endereço</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>CEP:</strong> {{ $usuario->cep ?? '-' }}</p>
                            <p><strong>Logradouro:</strong> {{ $usuario->logradouro ?? '-' }}</p>
                            <p><strong>Número:</strong> {{ $usuario->numero ?? '-' }}</p>
                            <p><strong>Complemento:</strong> {{ $usuario->complemento ?? '-' }}</p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Bairro:</strong> {{ $usuario->bairro ?? '-' }}</p>
                            <p><strong>Cidade:</strong> {{ $usuario->cidade ?? '-' }}</p>
                            <p><strong>Estado:</strong> {{ $usuario->estado ?? '-' }}</p>
                            <p><strong>Endereço Completo:</strong> {{ $usuario->endereco_completo ?? '-' }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Resumo de Pedidos -->
        <div class="col-md-4">
            <div class="admin-card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-box-seam"></i> Resumo de Compras</h5>
                </div>
                <div class="card-body">
                    <div class="text-center mb-3">
                        <div class="display-6 fw-bold text-primary">{{ $totalPedidos ?? 0 }}</div>
                        <span class="text-muted">Total de Pedidos</span>
                    </div>
                    <div class="text-center mb-3">
                        <div class="display-6 fw-bold text-success">R$ {{ number_format($totalGasto ?? 0, 2, ',', '.') }}</div>
                        <span class="text-muted">Total Gasto</span>
                    </div>
                    
                    @if(isset($ultimoPedido))
                        <hr>
                        <p class="mb-1"><strong>Último Pedido:</strong></p>
                        <p class="mb-1">#{{ $ultimoPedido->numero_pedido }}</p>
                        <p class="mb-0 text-muted small">{{ $ultimoPedido->created_at->format('d/m/Y H:i') }}</p>
                    @endif
                </div>
            </div>

            @if($usuario->trashed())
                <div class="admin-card border-danger">
                    <div class="card-body text-center">
                        <i class="bi bi-trash3 text-danger fs-1 d-block mb-2"></i>
                        <h5 class="text-danger">Usuário Deletado</h5>
                        <p class="text-muted small">Deletado em: {{ $usuario->deleted_at->format('d/m/Y H:i') }}</p>
                        <form action="{{ route('admin.usuarios.restore', $usuario->id) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-success">
                                <i class="bi bi-arrow-counterclockwise"></i> Restaurar
                            </button>
                        </form>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection