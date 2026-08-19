{{-- resources/views/checkout/pagamento/sucesso.blade.php --}}
<x-app-layout>
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card shadow-lg border-0 rounded-4">
                    <div class="card-body text-center p-5">
                        <!-- Ícone de Sucesso -->
                        <div class="mb-4">
                            <div class="success-icon bg-success bg-opacity-10 rounded-circle d-inline-flex p-4">
                                <i class="fas fa-check-circle text-success" style="font-size: 80px;"></i>
                            </div>
                        </div>

                        <h2 class="fw-bold mb-3">Pagamento Confirmado! 🎉</h2>
                        <p class="text-muted fs-5 mb-4">
                            Seu pedido foi processado com sucesso.
                        </p>

                        <!-- Detalhes do Pedido -->
                        <div class="card bg-light border-0 mb-4">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <p class="text-muted mb-1">Número do Pedido</p>
                                        <p class="fw-bold fs-5">#{{ $pedido->numero_pedido }}</p>
                                    </div>
                                    <div class="col-md-6">
                                        <p class="text-muted mb-1">Status do Pagamento</p>
                                        <p class="fw-bold fs-5 text-success">
                                            <i class="fas fa-check-circle me-1"></i> PAGO
                                        </p>
                                    </div>
                                </div>
                                <hr>
                                <div class="row">
                                    <div class="col-md-6">
                                        <p class="text-muted mb-1">Valor Pago</p>
                                        <p class="fw-bold fs-5 text-success">
                                            R$ {{ number_format($pedido->total, 2, ',', '.') }}
                                        </p>
                                    </div>
                                    <div class="col-md-6">
                                        <p class="text-muted mb-1">Data</p>
                                        <p class="fw-bold">{{ $pedido->data_pagamento ? $pedido->data_pagamento->format('d/m/Y H:i') : now()->format('d/m/Y H:i') }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Próximos Passos -->
                        <div class="alert alert-success">
                            <i class="fas fa-truck me-2"></i>
                            <strong>Seu pedido está sendo preparado para envio!</strong>
                            <br>
                            <small>Você receberá um email com as informações de rastreamento.</small>
                        </div>

                        <!-- Botões -->
                        <div class="d-flex flex-wrap justify-content-center gap-3 mt-4">
                            <a href="{{ route('checkout.detalhes', $pedido) }}" 
                               class="btn btn-success btn-lg">
                                <i class="fas fa-file-invoice me-2"></i> Ver Detalhes do Pedido
                            </a>
                            <a href="{{ route('home') }}" 
                               class="btn btn-outline-secondary btn-lg">
                                <i class="fas fa-home me-2"></i> Voltar para a Loja
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('styles')
    <style>
        .success-icon {
            width: 120px;
            height: 120px;
        }
        .rounded-4 {
            border-radius: 1rem !important;
        }
    </style>
    @endpush
</x-app-layout>