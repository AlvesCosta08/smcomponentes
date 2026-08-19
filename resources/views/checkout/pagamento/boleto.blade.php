{{-- resources/views/checkout/pagamento/boleto.blade.php --}}
<x-app-layout>
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <!-- Card Principal -->
                <div class="card shadow-lg border-0 rounded-4">
                    <div class="card-header bg-primary text-white rounded-top-4 py-3">
                        <h4 class="mb-0">
                            <i class="fas fa-barcode me-2"></i> Boleto Bancário
                        </h4>
                    </div>
                    
                    <div class="card-body p-4">
                        <!-- Informações do Pedido -->
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <h6 class="text-muted">Número do Pedido</h6>
                                <p class="fw-bold fs-5">#{{ $pedido->numero_pedido }}</p>
                            </div>
                            <div class="col-md-6 text-md-end">
                                <h6 class="text-muted">Valor Total</h6>
                                <p class="fw-bold fs-5 text-primary">R$ {{ number_format($pedido->total, 2, ',', '.') }}</p>
                            </div>
                        </div>

                        <hr>

                        <!-- Código de Barras -->
                        <div class="text-center my-4">
                            <h5 class="mb-3">Código de Barras</h5>
                            <div class="p-3 bg-light rounded-3">
                                <p class="font-monospace fs-4 fw-bold">
                                    {{ $boletoData['barcode'] ?? '00000000000000000000000000000000000000000000' }}
                                </p>
                            </div>
                        </div>

                        <!-- Informações do Boleto -->
                        <div class="row mt-4 g-3">
                            <div class="col-md-6">
                                <div class="card h-100 border-0 bg-light">
                                    <div class="card-body">
                                        <h6 class="text-muted"><i class="fas fa-calendar-alt me-2"></i>Vencimento</h6>
                                        <p class="fw-bold">
                                            {{ isset($boletoData['due_date']) ? date('d/m/Y', strtotime($boletoData['due_date'])) : '03/09/2026' }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card h-100 border-0 bg-light">
                                    <div class="card-body">
                                        <h6 class="text-muted"><i class="fas fa-money-bill-wave me-2"></i>Valor</h6>
                                        <p class="fw-bold text-primary">
                                            R$ {{ number_format($pedido->total, 2, ',', '.') }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Botões de Ação -->
                        <div class="mt-4 d-grid gap-2">
                            <a href="{{ $boletoData['boleto_url'] ?? '#' }}" 
                               target="_blank" 
                               class="btn btn-primary btn-lg">
                                <i class="fas fa-file-pdf me-2"></i> Visualizar Boleto
                            </a>
                            <a href="{{ $boletoData['boleto_url'] ?? '#' }}" 
                               download
                               class="btn btn-outline-primary">
                                <i class="fas fa-download me-2"></i> Baixar Boleto
                            </a>
                        </div>

                        <!-- Aviso -->
                        <div class="alert alert-warning mt-4">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <strong>Atenção:</strong> O boleto vence em até 3 dias úteis. Após o vencimento, o pedido será cancelado.
                        </div>

                        <!-- Botão Acompanhar -->
                        <div class="mt-4 text-center">
                            <a href="{{ route('checkout.sucesso', $pedido) }}" 
                               class="btn btn-success btn-lg px-5">
                                <i class="fas fa-check-circle me-2"></i> Já paguei o boleto
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Botão Voltar -->
                <div class="mt-4 text-center">
                    <a href="{{ route('checkout.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-2"></i> Voltar ao Checkout
                    </a>
                </div>
            </div>
        </div>
    </div>

    @push('styles')
    <style>
        .rounded-top-4 {
            border-top-left-radius: 1rem !important;
            border-top-right-radius: 1rem !important;
        }
        .rounded-4 {
            border-radius: 1rem !important;
        }
        .font-monospace {
            letter-spacing: 2px;
        }
    </style>
    @endpush
</x-app-layout>