{{-- resources/views/checkout/pagamento/pendente.blade.php --}}
<x-app-layout>
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card shadow-lg border-0 rounded-4">
                    <div class="card-body text-center p-5">
                        <!-- Ícone de Pendente -->
                        <div class="mb-4">
                            <div class="pending-icon bg-warning bg-opacity-10 rounded-circle d-inline-flex p-4">
                                <i class="fas fa-clock text-warning" style="font-size: 80px;"></i>
                            </div>
                        </div>

                        <h2 class="fw-bold mb-3">Pagamento em Análise ⏳</h2>
                        <p class="text-muted fs-5 mb-4">
                            Seu pagamento está sendo processado. Aguarde a confirmação.
                        </p>

                        <!-- Detalhes -->
                        <div class="card bg-light border-0 mb-4">
                            <div class="card-body">
                                <p class="text-muted mb-1">Número do Pedido</p>
                                <p class="fw-bold fs-5">#{{ $pedido->numero_pedido }}</p>
                                <hr>
                                <p class="text-muted mb-1">Status</p>
                                <p class="fw-bold text-warning">
                                    <i class="fas fa-spinner fa-spin me-1"></i> AGUARDANDO CONFIRMAÇÃO
                                </p>
                            </div>
                        </div>

                        <!-- Botões -->
                        <div class="d-flex flex-wrap justify-content-center gap-3 mt-4">
                            <a href="{{ route('checkout.detalhes', $pedido) }}" 
                               class="btn btn-warning btn-lg">
                                <i class="fas fa-sync me-2"></i> Verificar Status
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
        .pending-icon {
            width: 120px;
            height: 120px;
        }
        .rounded-4 {
            border-radius: 1rem !important;
        }
    </style>
    @endpush
</x-app-layout>