@extends('layouts.app')

@section('title', 'Meu Perfil - SM Componentes')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0"><i class="bi bi-person-circle"></i> Meu Perfil</h4>
                </div>
                <div class="card-body">
                    <!-- Informações do Usuário -->
                    <div class="text-center mb-4">
                        <div class="bg-light rounded-circle d-flex align-items-center justify-content-center mx-auto" 
                             style="width: 120px; height: 120px; border: 3px solid #2563eb;">
                            <i class="bi bi-person" style="font-size: 4rem; color: #2563eb;"></i>
                        </div>
                        <h5 class="mt-3">{{ Auth::user()->name }}</h5>
                        <p class="text-muted">{{ Auth::user()->email }}</p>
                        <span class="badge bg-success">Conta verificada</span>
                    </div>

                    @if(session('status'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="bi bi-check-circle-fill me-2"></i> 
                            @if(session('status') === 'profile-updated')
                                Perfil atualizado com sucesso!
                            @elseif(session('status') === 'password-updated')
                                Senha atualizada com sucesso!
                            @else
                                {{ session('status') }}
                            @endif
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="bi bi-exclamation-triangle-fill me-2"></i>
                            <ul class="mb-0 ps-3">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <!-- ============================================ -->
                    <!-- 1. FORMULÁRIO DE ATUALIZAÇÃO DE PERFIL -->
                    <!-- ============================================ -->
                    <h5 class="mb-3"><i class="bi bi-person-gear text-primary"></i> Dados do Perfil</h5>
                    <form method="POST" action="{{ route('profile.update') }}">
                        @csrf
                        @method('PATCH')

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="name" class="form-label fw-bold">Nome *</label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                           id="name" name="name" value="{{ old('name', Auth::user()->name) }}" required>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="email" class="form-label fw-bold">E-mail *</label>
                                    <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                           id="email" name="email" value="{{ old('email', Auth::user()->email) }}" required>
                                    @error('email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="telefone" class="form-label fw-bold">Telefone</label>
                                    <input type="text" class="form-control @error('telefone') is-invalid @enderror" 
                                           id="telefone" name="telefone" 
                                           value="{{ old('telefone', Auth::user()->telefone_formatado ?? '') }}" 
                                           placeholder="(11) 9999-9999" maxlength="15">
                                    @error('telefone')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="celular" class="form-label fw-bold">Celular</label>
                                    <input type="text" class="form-control @error('celular') is-invalid @enderror" 
                                           id="celular" name="celular" 
                                           value="{{ old('celular', Auth::user()->celular_formatado ?? '') }}" 
                                           placeholder="(11) 99999-9999" maxlength="16">
                                    @error('celular')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        {{-- ❌ CPF REMOVIDO --}}
                        {{-- ❌ CNPJ REMOVIDO --}}

                        <div class="mb-3">
                            <label for="data_nascimento" class="form-label fw-bold">Data de Nascimento</label>
                            <input type="date" class="form-control @error('data_nascimento') is-invalid @enderror" 
                                   id="data_nascimento" name="data_nascimento" 
                                   value="{{ old('data_nascimento', Auth::user()->data_nascimento?->format('Y-m-d')) }}">
                            @error('data_nascimento')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <h5 class="mb-3 mt-3"><i class="bi bi-geo-alt text-primary"></i> Endereço</h5>

                        <div class="mb-3">
                            <label for="cep" class="form-label fw-bold">CEP</label>
                            <input type="text" class="form-control @error('cep') is-invalid @enderror" 
                                   id="cep" name="cep" 
                                   value="{{ old('cep', Auth::user()->cep_formatado ?? '') }}" 
                                   placeholder="00000-000" maxlength="10">
                            @error('cep')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="logradouro" class="form-label fw-bold">Logradouro</label>
                            <input type="text" class="form-control @error('logradouro') is-invalid @enderror" 
                                   id="logradouro" name="logradouro" 
                                   value="{{ old('logradouro', Auth::user()->logradouro ?? '') }}" 
                                   placeholder="Rua, Avenida, ...">
                            @error('logradouro')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="numero" class="form-label fw-bold">Número</label>
                                    <input type="text" class="form-control @error('numero') is-invalid @enderror" 
                                           id="numero" name="numero" 
                                           value="{{ old('numero', Auth::user()->numero ?? '') }}" 
                                           placeholder="123">
                                    @error('numero')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-8">
                                <div class="mb-3">
                                    <label for="complemento" class="form-label fw-bold">Complemento</label>
                                    <input type="text" class="form-control @error('complemento') is-invalid @enderror" 
                                           id="complemento" name="complemento" 
                                           value="{{ old('complemento', Auth::user()->complemento ?? '') }}" 
                                           placeholder="Apto, Bloco, ...">
                                    @error('complemento')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="bairro" class="form-label fw-bold">Bairro</label>
                            <input type="text" class="form-control @error('bairro') is-invalid @enderror" 
                                   id="bairro" name="bairro" 
                                   value="{{ old('bairro', Auth::user()->bairro ?? '') }}" 
                                   placeholder="Bairro">
                            @error('bairro')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-8">
                                <div class="mb-3">
                                    <label for="cidade" class="form-label fw-bold">Cidade</label>
                                    <input type="text" class="form-control @error('cidade') is-invalid @enderror" 
                                           id="cidade" name="cidade" 
                                           value="{{ old('cidade', Auth::user()->cidade ?? '') }}" 
                                           placeholder="Cidade">
                                    @error('cidade')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="estado" class="form-label fw-bold">UF</label>
                                    <input type="text" class="form-control @error('estado') is-invalid @enderror" 
                                           id="estado" name="estado" 
                                           value="{{ old('estado', Auth::user()->estado ?? '') }}" 
                                           maxlength="2" placeholder="SP">
                                    @error('estado')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between mt-3">
                            <a href="{{ route('home') }}" class="btn btn-secondary">
                                <i class="bi bi-arrow-left"></i> Voltar
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save"></i> Salvar Perfil
                            </button>
                        </div>
                    </form>

                    <hr class="my-4">

                    <!-- ============================================ -->
                    <!-- 2. FORMULÁRIO DE ATUALIZAÇÃO DE SENHA -->
                    <!-- ============================================ -->
                    <h5 class="mb-3"><i class="bi bi-shield-lock text-primary"></i> Atualizar Senha</h5>
                    <form method="POST" action="{{ route('password.update') }}">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label for="current_password" class="form-label fw-bold">Senha Atual *</label>
                            <input type="password" class="form-control @error('current_password') is-invalid @enderror" 
                                   id="current_password" name="current_password" required>
                            @error('current_password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="password" class="form-label fw-bold">Nova Senha *</label>
                                    <input type="password" class="form-control @error('password') is-invalid @enderror" 
                                           id="password" name="password" required>
                                    @error('password')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="password_confirmation" class="form-label fw-bold">Confirmar Nova Senha *</label>
                                    <input type="password" class="form-control" 
                                           id="password_confirmation" name="password_confirmation" required>
                                </div>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-warning">
                            <i class="bi bi-key"></i> Atualizar Senha
                        </button>
                    </form>

                    <hr class="my-4">

                    <!-- ============================================ -->
                    <!-- 3. EXCLUIR CONTA -->
                    <!-- ============================================ -->
                    <div class="text-center">
                        <h6 class="text-danger"><i class="bi bi-exclamation-triangle"></i> Zona de Perigo</h6>
                        <p class="text-muted small">Esta ação é permanente e não pode ser desfeita.</p>
                        <form action="{{ route('profile.destroy') }}" method="POST" 
                              onsubmit="return confirm('Tem certeza que deseja excluir sua conta? Esta ação é irreversível!')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-outline-danger">
                                <i class="bi bi-trash"></i> Excluir Conta
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Máscara para Telefone
    function mascaraTelefone(input) {
        let value = input.value.replace(/\D/g, '');
        if (value.length > 10) value = value.slice(0, 10);
        if (value.length > 6) {
            value = value.replace(/^(\d{2})(\d{4})(\d{0,4})/, '($1) $2-$3');
        } else if (value.length > 2) {
            value = value.replace(/^(\d{2})(\d{0,4})/, '($1) $2');
        }
        input.value = value;
    }

    // Máscara para Celular
    function mascaraCelular(input) {
        let value = input.value.replace(/\D/g, '');
        if (value.length > 11) value = value.slice(0, 11);
        if (value.length > 7) {
            value = value.replace(/^(\d{2})(\d{5})(\d{0,4})/, '($1) $2-$3');
        } else if (value.length > 2) {
            value = value.replace(/^(\d{2})(\d{0,5})/, '($1) $2');
        }
        input.value = value;
    }

    // Máscara para CEP
    function mascaraCEP(input) {
        let value = input.value.replace(/\D/g, '');
        if (value.length > 8) value = value.slice(0, 8);
        if (value.length > 5) {
            value = value.replace(/^(\d{5})(\d{0,3})/, '$1-$2');
        }
        input.value = value;
    }

    // Aplicar máscaras
    const telefone = document.getElementById('telefone');
    if (telefone) {
        telefone.addEventListener('input', function() { mascaraTelefone(this); });
    }

    const celular = document.getElementById('celular');
    if (celular) {
        celular.addEventListener('input', function() { mascaraCelular(this); });
    }

    const cep = document.getElementById('cep');
    if (cep) {
        cep.addEventListener('input', function() { mascaraCEP(this); });
        
        // Buscar CEP
        cep.addEventListener('blur', function() {
            const value = this.value.replace(/\D/g, '');
            if (value.length === 8) {
                fetch(`https://viacep.com.br/ws/${value}/json/`)
                    .then(response => response.json())
                    .then(data => {
                        if (!data.erro) {
                            const logradouro = document.getElementById('logradouro');
                            const bairro = document.getElementById('bairro');
                            const cidade = document.getElementById('cidade');
                            const estado = document.getElementById('estado');
                            if (logradouro) logradouro.value = data.logradouro || '';
                            if (bairro) bairro.value = data.bairro || '';
                            if (cidade) cidade.value = data.localidade || '';
                            if (estado) estado.value = data.uf || '';
                        }
                    })
                    .catch(error => console.log('Erro ao buscar CEP:', error));
            }
        });
    }
});
</script>
@endpush
