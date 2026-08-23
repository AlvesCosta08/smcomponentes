@extends('layouts.app')

@section('title', 'Cadastro - SM Componentes')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-11 col-sm-10 col-md-8 col-lg-5 col-xl-4">
            <div class="card border-0 shadow-lg">
                <div class="card-body p-4 p-sm-5">
                    <!-- Header -->
                    <div class="text-center mb-4">
                        <div class="bg-primary rounded-circle d-inline-flex align-items-center justify-content-center mb-3" 
                             style="width: 60px; height: 60px; background: linear-gradient(135deg, #2563eb, #1d4ed8) !important;">
                            <i class="bi bi-person-plus text-white fs-2"></i>
                        </div>
                        <h4 class="fw-bold mb-1" style="color: #0f172a;">Criar conta</h4>
                        <p class="text-muted small">Preencha os dados abaixo para se cadastrar</p>
                    </div>

                    @if ($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show small" role="alert">
                            <i class="bi bi-exclamation-triangle-fill me-1"></i> {{ $errors->first() }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <!-- Form -->
                    <form method="POST" action="{{ route('register') }}" id="register-form">
                        @csrf

                        <div class="mb-3">
                            <label for="name" class="form-label fw-semibold small" style="color: #334155;">
                                <i class="bi bi-person me-1" style="color: #2563eb;"></i> Nome completo
                            </label>
                            <input id="name" type="text" name="name" 
                                   class="form-control @error('name') is-invalid @enderror" 
                                   value="{{ old('name') }}" required autofocus
                                   placeholder="Seu nome completo">
                            @error('name')
                                <div class="invalid-feedback small">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label fw-semibold small" style="color: #334155;">
                                <i class="bi bi-envelope me-1" style="color: #2563eb;"></i> E-mail
                            </label>
                            <input id="email" type="email" name="email" 
                                   class="form-control @error('email') is-invalid @enderror" 
                                   value="{{ old('email') }}" required
                                   placeholder="seu@email.com">
                            @error('email')
                                <div class="invalid-feedback small">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label fw-semibold small" style="color: #334155;">
                                <i class="bi bi-lock me-1" style="color: #2563eb;"></i> Senha
                            </label>
                            <input id="password" type="password" name="password" 
                                   class="form-control @error('password') is-invalid @enderror" 
                                   required placeholder="••••••••">
                            @error('password')
                                <div class="invalid-feedback small">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="password_confirmation" class="form-label fw-semibold small" style="color: #334155;">
                                <i class="bi bi-lock-fill me-1" style="color: #2563eb;"></i> Confirmar senha
                            </label>
                            <input id="password_confirmation" type="password" 
                                   name="password_confirmation" class="form-control" 
                                   required placeholder="••••••••">
                        </div>

                        <button type="submit" class="btn w-100 py-2" style="background: linear-gradient(135deg, #2563eb, #1d4ed8); color: #ffffff; border: none; border-radius: 10px; font-weight: 600; transition: all 0.3s ease;">
                            <i class="bi bi-person-plus me-2"></i> Cadastrar
                        </button>
                    </form>

                    <!-- Footer -->
                    <div class="text-center mt-4 pt-3 border-top" style="border-color: #e2e8f0 !important;">
                        <span class="text-muted small">Já tem uma conta?</span>
                        <a href="{{ route('login') }}" class="text-decoration-none fw-semibold small" style="color: #2563eb;">
                            Faça login <i class="bi bi-arrow-right"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('register-form');
    
    form.addEventListener('submit', function(e) {
        const token = document.querySelector('meta[name="csrf-token"]');
        const csrfInput = this.querySelector('input[name="_token"]');
        
        if (!token || !csrfInput || !csrfInput.value) {
            e.preventDefault();
            alert('Erro de segurança: Token CSRF não encontrado. Recarregue a página.');
            window.location.reload();
        }
    });
});
</script>
@endpush

@push('styles')
<style>
    .card {
        border-radius: 16px;
        transition: transform 0.3s ease;
        border: none;
        overflow: hidden;
    }
    .card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: linear-gradient(90deg, #2563eb, #f97316);
    }
    .card:hover {
        transform: translateY(-4px);
        box-shadow: 0 20px 60px rgba(0,0,0,0.12) !important;
    }
    .form-control {
        border-radius: 10px;
        padding: 12px 16px;
        border: 2px solid #e2e8f0;
        transition: all 0.3s ease;
        font-size: 0.95rem;
    }
    .form-control:focus {
        border-color: #2563eb;
        box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.12);
    }
    .form-control.is-invalid {
        border-color: #ef4444;
        box-shadow: 0 0 0 4px rgba(239, 68, 68, 0.12);
    }
    .form-control.is-invalid:focus {
        box-shadow: 0 0 0 4px rgba(239, 68, 68, 0.15);
    }
    .btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(37, 99, 235, 0.3);
    }
    .btn:active {
        transform: scale(0.98);
    }
    .alert {
        border-radius: 10px;
        border: none;
    }
    .alert-danger {
        background: #fef2f2;
        color: #dc2626;
    }
</style>
@endpush
@endsection