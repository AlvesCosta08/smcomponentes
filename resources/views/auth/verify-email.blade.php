@extends('layouts.app')

@section('title', 'Verificar Email - SM Componentes')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-11 col-sm-10 col-md-8 col-lg-5 col-xl-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4 p-sm-5">
                    <!-- Header -->
                    <div class="text-center mb-4">
                        <div class="bg-primary bg-gradient rounded-circle d-inline-flex align-items-center justify-content-center mb-3" 
                             style="width: 60px; height: 60px;">
                            <i class="bi bi-envelope-check text-white fs-2"></i>
                        </div>
                        <h4 class="fw-bold mb-1">Verifique seu email</h4>
                        <p class="text-muted small">Confirme sua conta</p>
                    </div>

                    <div class="alert alert-info small">
                        <i class="bi bi-info-circle-fill me-1"></i> 
                        Enviamos um link de verificação para seu email.
                    </div>

                    @if (session('status') == 'verification-link-sent')
                        <div class="alert alert-success alert-dismissible fade show small" role="alert">
                            <i class="bi bi-check-circle-fill me-1"></i> 
                            Novo link enviado para seu email.
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <!-- Form -->
                    <form method="POST" action="{{ route('verification.send') }}">
                        @csrf
                        <button type="submit" class="btn btn-primary w-100 py-2">
                            <i class="bi bi-envelope-paper me-2"></i> Reenviar
                        </button>
                    </form>

                    <!-- Footer -->
                    <div class="text-center mt-4 pt-3 border-top">
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="btn btn-link text-decoration-none small p-0 border-0">
                                <i class="bi bi-box-arrow-right me-1"></i> Sair
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    .card { border-radius: 16px; transition: transform .2s; }
    .card:hover { transform: translateY(-2px); }
    .btn-primary {
        border-radius: 10px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border: none;
        transition: all .3s;
        font-weight: 600;
    }
    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(102,126,234,.4);
    }
    .bg-primary.bg-gradient {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
    }
</style>
@endpush
@endsection
