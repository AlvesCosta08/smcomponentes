@extends('layouts.app')

@section('title', 'Finalizar Compra - SM Componentes')

@section('content')
<div class="container py-4">
    <div class="row">
        <div class="col-lg-8">
            <h2 class="fw-bold mb-4">Finalizar Pedido</h2>

            @if(session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif

            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            <div class="card shadow-sm">
                <div class="card-header">
                    <h5>Itens do Carrinho ({{ count($carrinho) }})</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Produto</th>
                                    <th class="text-center">Qtd</th>
                                    <th class="text-end">Preço</th>
                                    <th class="text-end">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if(count($carrinho) > 0)
                                    @foreach($carrinho as $item)
                                    <tr>
                                        <td>{{ $item['nome'] ?? 'Produto' }}</td>
                                        <td class="text-center">{{ $item['quantidade'] }}</td>
                                        <td class="text-end">{{ $item['preco_formatado'] ?? 'R$ 0,00' }}</td>
                                        <td class="text-end">{{ $item['subtotal_formatado'] ?? 'R$ 0,00' }}</td>
                                    </tr>
                                    @endforeach
                                @else
                                    <tr>
                                        <td colspan="4" class="text-center">Carrinho vazio</td>
                                    </tr>
                                @endif
                            </tbody>
                            @if(count($carrinho) > 0)
                            <tfoot>
                                <tr>
                                    <td colspan="3" class="text-end"><strong>Total:</strong></td>
                                    <td class="text-end"><strong>R$ {{ number_format($total ?? 0, 2, ',', '.') }}</strong></td>
                                </tr>
                            </tfoot>
                            @endif
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card shadow-sm">
                <div class="card-header">
                    <h5>Pagamento</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('checkout.processar') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Forma de pagamento</label>
                            <div class="d-grid gap-2">
                                <div>
                                    <input type="radio" name="forma_pagamento" value="pix" id="pix" checked>
                                    <label for="pix">PIX</label>
                                </div>
                                <div>
                                    <input type="radio" name="forma_pagamento" value="boleto" id="boleto">
                                    <label for="boleto">Boleto</label>
                                </div>
                                <div>
                                    <input type="radio" name="forma_pagamento" value="cartao" id="cartao">
                                    <label for="cartao">Cartão de Crédito</label>
                                </div>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-success w-100">Finalizar Compra</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
