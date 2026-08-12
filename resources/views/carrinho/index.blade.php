@extends('layouts.app')

@section('title', 'Carrinho de Compras - SM Componentes')

@section('content')
<div class="container py-4">
    <h2 class="mb-4"><i class="bi bi-cart3"></i> Carrinho de Compras</h2>
    
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle-fill"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle-fill"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    
    @if(empty($carrinho) || count($carrinho) == 0)
        <div class="text-center py-5">
            <i class="bi bi-cart-x" style="font-size: 5rem; color: #ddd;"></i>
            <h3 class="mt-3">Seu carrinho está vazio</h3>
            <p class="text-muted">Que tal dar uma olhada em nossos produtos?</p>
            <a href="{{ route('produtos.index') }}" class="btn btn-primary btn-lg mt-2">
                <i class="bi bi-grid"></i> Ver Produtos
            </a>
        </div>
    @else
        <div class="row">
            <div class="col-lg-8">
                <div class="card shadow-sm">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 40%;">Produto</th>
                                        <th class="text-center" style="width: 15%;">Preço</th>
                                        <th class="text-center" style="width: 25%;">Quantidade</th>
                                        <th class="text-end" style="width: 15%;">Subtotal</th>
                                        <th class="text-center" style="width: 5%;">Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($carrinho as $index => $item)
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="bg-light rounded d-flex align-items-center justify-content-center" 
                                                         style="width: 50px; height: 50px; flex-shrink: 0;">
                                                        <i class="bi bi-plug" style="font-size: 1.5rem; color: #ccc;"></i>
                                                    </div>
                                                    <div class="ms-3">
                                                        <h6 class="mb-0">{{ Str::limit($item['nome'] ?? 'Produto', 50) }}</h6>
                                                        <small class="text-muted">Código: #{{ $item['produto_id'] ?? 'N/A' }}</small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="text-center fw-bold">
                                                R$ {{ number_format($item['preco'] ?? 0, 2, ',', '.') }}
                                            </td>
                                            <td>
                                                <form action="{{ route('carrinho.atualizar', $index) }}" method="POST" class="d-flex justify-content-center align-items-center gap-2">
                                                    @csrf
                                                    @method('PUT')
                                                    <div class="input-group" style="width: 120px;">
                                                        <button type="button" class="btn btn-outline-secondary" onclick="decrementar(this)">
                                                            <i class="bi bi-dash"></i>
                                                        </button>
                                                        <input type="number" 
                                                               name="quantidade" 
                                                               value="{{ $item['quantidade'] }}" 
                                                               min="1" 
                                                               max="{{ $item['estoque'] ?? 999 }}"
                                                               class="form-control text-center"
                                                               style="width: 50px;"
                                                               onchange="this.form.submit()">
                                                        <button type="button" class="btn btn-outline-secondary" onclick="incrementar(this)">
                                                            <i class="bi bi-plus"></i>
                                                        </button>
                                                    </div>
                                                    <button type="submit" class="btn btn-sm btn-primary" title="Atualizar">
                                                        <i class="bi bi-arrow-clockwise"></i>
                                                    </button>
                                                </form>
                                            </td>
                                            <td class="text-end fw-bold text-primary">
                                                R$ {{ number_format($item['subtotal'] ?? 0, 2, ',', '.') }}
                                            </td>
                                            <td class="text-center">
                                                <form action="{{ route('carrinho.remover', $index) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-outline-danger" 
                                                            onclick="return confirm('Remover este item do carrinho?')">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="d-flex flex-wrap gap-2 mt-3">
                    <a href="{{ route('produtos.index') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left"></i> Continuar Comprando
                    </a>
                    <form action="{{ route('carrinho.limpar') }}" method="POST" class="d-inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-outline-danger" onclick="return confirm('Limpar todo o carrinho?')">
                            <i class="bi bi-cart-x"></i> Limpar Carrinho
                        </button>
                    </form>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title"><i class="bi bi-receipt"></i> Resumo do Pedido</h5>
                        <hr>
                        
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Subtotal:</span>
                            <span class="fw-bold">R$ {{ number_format($total ?? 0, 2, ',', '.') }}</span>
                        </div>
                        
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Frete:</span>
                            <span class="text-success">A calcular</span>
                        </div>
                        
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Desconto:</span>
                            <span class="text-danger">R$ 0,00</span>
                        </div>
                        
                        <hr>
                        
                        <div class="d-flex justify-content-between mb-3">
                            <span class="h5">Total:</span>
                            <span class="h5 fw-bold text-primary">R$ {{ number_format($total ?? 0, 2, ',', '.') }}</span>
                        </div>
                        
                        <button class="btn btn-success btn-lg w-100" 
                                onclick="alert('🚀 Sistema de checkout em desenvolvimento!')">
                            <i class="bi bi-cart-check"></i> Finalizar Compra
                        </button>
                        
                        <div class="mt-3">
                            <small class="text-muted d-block text-center">
                                <i class="bi bi-shield-check text-success"></i> Compra 100% segura
                            </small>
                            <small class="text-muted d-block text-center mt-1">
                                <i class="bi bi-credit-card"></i> Parcelamos em até 6x sem juros
                            </small>
                        </div>
                    </div>
                </div>

                <div class="card shadow-sm mt-3">
                    <div class="card-body">
                        <h6><i class="bi bi-truck text-primary"></i> Formas de Entrega</h6>
                        <p class="text-muted small mb-0">
                            Consulte o frete no momento da finalização da compra.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>

<style>
    input[type="number"]::-webkit-inner-spin-button,
    input[type="number"]::-webkit-outer-spin-button {
        -webkit-appearance: none;
        margin: 0;
    }
    input[type="number"] {
        -moz-appearance: textfield;
    }
</style>

<script>
    function decrementar(btn) {
        let input = btn.parentElement.querySelector('input[name="quantidade"]');
        if (input.value > 1) {
            input.value = parseInt(input.value) - 1;
            input.form.submit();
        }
    }
    
    function incrementar(btn) {
        let input = btn.parentElement.querySelector('input[name="quantidade"]');
        let max = parseInt(input.max) || 999;
        if (parseInt(input.value) < max) {
            input.value = parseInt(input.value) + 1;
            input.form.submit();
        }
    }
</script>
@endsection