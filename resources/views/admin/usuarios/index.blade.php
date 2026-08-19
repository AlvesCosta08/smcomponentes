@extends('layouts.app')

@section('title', 'Usuários - Admin')

@section('content')
<div class="container-fluid px-4">
    <div class="page-header d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0"><i class="bi bi-people"></i> Usuários</h1>
            <small class="text-muted">Gerencie todos os usuários do sistema</small>
        </div>
        <div>
            <a href="{{ route('admin.usuarios.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> Novo Usuário
            </a>
        </div>
    </div>

    <!-- Estatísticas -->
    <div class="row g-4 mb-4">
        <div class="col-md-2">
            <div class="admin-stat-card">
                <div class="stat-number">{{ $totalUsuarios ?? 0 }}</div>
                <div class="stat-label">Total</div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="admin-stat-card border-success">
                <div class="stat-number text-success">{{ $totalClientes ?? 0 }}</div>
                <div class="stat-label">Clientes</div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="admin-stat-card border-primary">
                <div class="stat-number text-primary">{{ $totalAdmins ?? 0 }}</div>
                <div class="stat-label">Admins</div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="admin-stat-card border-warning">
                <div class="stat-number text-warning">{{ $totalFuncionarios ?? 0 }}</div>
                <div class="stat-label">Funcionários</div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="admin-stat-card border-info">
                <div class="stat-number text-info">{{ $usuariosAtivos ?? 0 }}</div>
                <div class="stat-label">Ativos</div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="admin-stat-card border-secondary">
                <div class="stat-number text-secondary">{{ $usuariosInativos ?? 0 }}</div>
                <div class="stat-label">Inativos</div>
            </div>
        </div>
    </div>

    <!-- Filtros -->
    <div class="admin-card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.usuarios.index') }}" class="row g-3">
                <div class="col-md-3">
                    <input type="text" name="search" class="form-control" placeholder="Buscar por nome, email ou CPF..." value="{{ request('search') }}">
                </div>
                <div class="col-md-2">
                    <select name="role" class="form-select">
                        <option value="">Todas Roles</option>
                        @foreach($roles as $role)
                            <option value="{{ $role }}" {{ request('role') == $role ? 'selected' : '' }}>
                                {{ $role }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="status" class="form-select">
                        <option value="">Status</option>
                        <option value="1" {{ request('status') == '1' ? 'selected' : '' }}>Ativo</option>
                        <option value="0" {{ request('status') == '0' ? 'selected' : '' }}>Inativo</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <input type="date" name="data_inicio" class="form-control" placeholder="Data Início" value="{{ request('data_inicio') }}">
                </div>
                <div class="col-md-2">
                    <input type="date" name="data_fim" class="form-control" placeholder="Data Fim" value="{{ request('data_fim') }}">
                </div>
                <div class="col-md-1">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-search"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Lista de Usuários -->
    <div class="admin-card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover admin-table mb-0">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nome</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Pedidos</th>
                            <th>Cadastro</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($usuarios as $usuario)
                            <tr>
                                <td>#{{ $usuario->id }}</td>
                                <td>
                                    <strong>{{ $usuario->name }}</strong>
                                    <br>
                                    <small class="text-muted">{{ $usuario->cpf ?? 'Sem CPF' }}</small>
                                </td>
                                <td>{{ $usuario->email }}</td>
                                <td>
                                    @php
                                        $roleColors = ['Admin' => 'danger', 'Funcionario' => 'warning', 'Cliente' => 'info'];
                                        $roleName = $usuario->roles->first()->name ?? 'Sem Role';
                                    @endphp
                                    <span class="badge bg-{{ $roleColors[$roleName] ?? 'secondary' }}">
                                        {{ $roleName }}
                                    </span>
                                </td>
                                <td>
                                    @if($usuario->ativo)
                                        <span class="badge bg-success">Ativo</span>
                                    @else
                                        <span class="badge bg-danger">Inativo</span>
                                    @endif
                                    @if($usuario->trashed())
                                        <span class="badge bg-dark">🗑️ Deletado</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-info">{{ $usuario->pedidos->count() }}</span>
                                </td>
                                <td>{{ $usuario->created_at->format('d/m/Y') }}</td>
                                <td>
                                    <div class="table-actions">
                                        <a href="{{ route('admin.usuarios.show', $usuario) }}" class="btn btn-sm btn-info" title="Ver">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="{{ route('admin.usuarios.edit', $usuario) }}" class="btn btn-sm btn-warning" title="Editar">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        
                                        @if($usuario->trashed())
                                            <form action="{{ route('admin.usuarios.restore', $usuario->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-success" title="Restaurar">
                                                    <i class="bi bi-arrow-counterclockwise"></i>
                                                </button>
                                            </form>
                                        @else
                                            <form action="{{ route('admin.usuarios.toggle-status', $usuario) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-sm {{ $usuario->ativo ? 'btn-danger' : 'btn-success' }}" title="{{ $usuario->ativo ? 'Desativar' : 'Ativar' }}">
                                                    <i class="bi {{ $usuario->ativo ? 'bi-person-x' : 'bi-person-check' }}"></i>
                                                </button>
                                            </form>
                                            
                                            @if($usuario->id !== auth()->id())
                                                <form action="{{ route('admin.usuarios.destroy', $usuario) }}" method="POST" class="d-inline" onsubmit="return confirm('Deseja realmente deletar este usuário?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-danger" title="Deletar">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </form>
                                            @endif
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-4">
                                    <i class="bi bi-inbox display-4 d-block text-muted"></i>
                                    <span class="text-muted">Nenhum usuário encontrado.</span>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer">
            {{ $usuarios->appends(request()->query())->links() }}
        </div>
    </div>
</div>
@endsection