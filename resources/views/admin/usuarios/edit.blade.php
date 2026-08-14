@extends('layouts.app')

@section('title', 'Editar Usuário - Admin')

@section('content')
<div class="container-fluid px-4">
    <div class="page-header d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0"><i class="bi bi-person-gear"></i> Editar Usuário</h1>
            <small class="text-muted">#{{ $usuario->id }} - {{ $usuario->name }}</small>
        </div>
        <div>
            <a href="{{ route('admin.usuarios.index') }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Voltar
            </a>
        </div>
    </div>

    <div class="admin-card">
        <div class="card-body">
            <form action="{{ route('admin.usuarios.update', $usuario) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="row">
                    <!-- Dados Pessoais -->
                    <div class="col-md-6">
                        <h5 class="mb-3"><i class="bi bi-person"></i> Dados Pessoais</h5>
                        
                        <div class="mb-3">
                            <label for="name" class="form-label">Nome *</label>
                            <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $usuario->name) }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label">Email *</label>
                            <input type="email" name="email" id="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $usuario->email) }}" required>
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label">Nova Senha</label>
                            <input type="password" name="password" id="password" class="form-control @error('password') is-invalid @enderror">
                            <small class="text-muted">Deixe em branco para manter a senha atual.</small>
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="password_confirmation" class="form-label">Confirmar Nova Senha</label>
                            <input type="password" name="password_confirmation" id="password_confirmation" class="form-control">
                        </div>

                        {{-- 🔥 CORRIGIDO: Usar $role diretamente --}}
                        <div class="mb-3">
                            <label for="role" class="form-label">Role *</label>
                            <select name="role" id="role" class="form-select @error('role') is-invalid @enderror" required>
                                <option value="">Selecione...</option>
                                @foreach($roles as $role)
                                    <option value="{{ $role }}" {{ old('role', $userRole) == $role ? 'selected' : '' }}>
                                        {{ $role }}
                                    </option>
                                @endforeach
                            </select>
                            @error('role')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="ativo" class="form-label">Status</label>
                            <select name="ativo" id="ativo" class="form-select @error('ativo') is-invalid @enderror">
                                <option value="1" {{ old('ativo', $usuario->ativo) == 1 ? 'selected' : '' }}>Ativo</option>
                                <option value="0" {{ old('ativo', $usuario->ativo) == 0 ? 'selected' : '' }}>Inativo</option>
                            </select>
                            @error('ativo')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Contato e Endereço -->
                    <div class="col-md-6">
                        <h5 class="mb-3"><i class="bi bi-telephone"></i> Contato</h5>
                        
                        <div class="mb-3">
                            <label for="telefone" class="form-label">Telefone</label>
                            <input type="text" name="telefone" id="telefone" class="form-control @error('telefone') is-invalid @enderror" value="{{ old('telefone', $usuario->telefone) }}">
                            @error('telefone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="celular" class="form-label">Celular</label>
                            <input type="text" name="celular" id="celular" class="form-control @error('celular') is-invalid @enderror" value="{{ old('celular', $usuario->celular) }}">
                            @error('celular')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="cpf" class="form-label">CPF</label>
                            <input type="text" name="cpf" id="cpf" class="form-control @error('cpf') is-invalid @enderror" value="{{ old('cpf', $usuario->cpf) }}">
                            @error('cpf')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="data_nascimento" class="form-label">Data de Nascimento</label>
                            <input type="date" name="data_nascimento" id="data_nascimento" class="form-control @error('data_nascimento') is-invalid @enderror" value="{{ old('data_nascimento', $usuario->data_nascimento?->format('Y-m-d')) }}">
                            @error('data_nascimento')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <h5 class="mb-3 mt-3"><i class="bi bi-geo-alt"></i> Endereço</h5>

                        <div class="mb-3">
                            <label for="cep" class="form-label">CEP</label>
                            <input type="text" name="cep" id="cep" class="form-control @error('cep') is-invalid @enderror" value="{{ old('cep', $usuario->cep) }}">
                            @error('cep')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="logradouro" class="form-label">Logradouro</label>
                            <input type="text" name="logradouro" id="logradouro" class="form-control @error('logradouro') is-invalid @enderror" value="{{ old('logradouro', $usuario->logradouro) }}">
                            @error('logradouro')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="numero" class="form-label">Número</label>
                                    <input type="text" name="numero" id="numero" class="form-control @error('numero') is-invalid @enderror" value="{{ old('numero', $usuario->numero) }}">
                                    @error('numero')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="complemento" class="form-label">Complemento</label>
                                    <input type="text" name="complemento" id="complemento" class="form-control @error('complemento') is-invalid @enderror" value="{{ old('complemento', $usuario->complemento) }}">
                                    @error('complemento')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="bairro" class="form-label">Bairro</label>
                            <input type="text" name="bairro" id="bairro" class="form-control @error('bairro') is-invalid @enderror" value="{{ old('bairro', $usuario->bairro) }}">
                            @error('bairro')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-8">
                                <div class="mb-3">
                                    <label for="cidade" class="form-label">Cidade</label>
                                    <input type="text" name="cidade" id="cidade" class="form-control @error('cidade') is-invalid @enderror" value="{{ old('cidade', $usuario->cidade) }}">
                                    @error('cidade')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="estado" class="form-label">UF</label>
                                    <input type="text" name="estado" id="estado" class="form-control @error('estado') is-invalid @enderror" value="{{ old('estado', $usuario->estado) }}" maxlength="2">
                                    @error('estado')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Botões -->
                    <div class="col-12 mt-3">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save"></i> Atualizar Usuário
                        </button>
                        <a href="{{ route('admin.usuarios.index') }}" class="btn btn-secondary">
                            <i class="bi bi-x-circle"></i> Cancelar
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection