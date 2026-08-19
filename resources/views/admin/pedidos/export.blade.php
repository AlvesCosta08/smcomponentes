@extends('layouts.app')

@section('title', 'Exportar Pedidos - Admin')

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h2 mb-0">📤 Exportar Pedidos</h1>
            <small class="text-muted">Exporte os pedidos em diferentes formatos</small>
        </div>
        <a href="{{ route('admin.pedidos.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Voltar
        </a>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="admin-card">
                <div class="card-body text-center py-5">
                    <i class="bi bi-file-excel text-success" style="font-size: 4rem;"></i>
                    <h4 class="mt-3">Exportar para Excel</h4>
                    <p class="text-muted">Baixe os dados em formato Excel (XLSX)</p>
                    <a href="{{ route('admin.pedidos.export') }}?format=excel" class="btn btn-success btn-lg">
                        <i class="bi bi-download"></i> Baixar Excel
                    </a>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="admin-card">
                <div class="card-body text-center py-5">
                    <i class="bi bi-file-pdf text-danger" style="font-size: 4rem;"></i>
                    <h4 class="mt-3">Exportar para PDF</h4>
                    <p class="text-muted">Baixe os dados em formato PDF</p>
                    <a href="{{ route('admin.pedidos.export') }}?format=pdf" class="btn btn-danger btn-lg">
                        <i class="bi bi-download"></i> Baixar PDF
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection