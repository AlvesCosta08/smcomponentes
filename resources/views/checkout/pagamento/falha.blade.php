{{-- resources/views/checkout/pagamento/falha.blade.php --}}
<x-app-layout>
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card shadow-lg border-0 rounded-4">
                    <div class="card-body text-center p-5">
                        <!-- Ícone de Falha -->
                        <div class="mb-4">
                            <div class="fail-icon bg-danger bg-opacity-10 rounded-circle d-inline-flex p-4">
                                <i class="fas fa-times-circle text-danger" style="font-size: 80px;"></i>
                            </div>
                        </div>

                        <h2 class="fw-bold mb-3">Pagamento não Confirmado 😕</h2>
                        <p class="text-muted fs-5 mb-4">
                            Ocorreu um problema ao processar seu pagamento.
                        </p>

                        <!-- Detalhes -->
                        <div class="card bg-light border-0 mb-4">
                            <div class="card-body">
                                <p class="text-muted mb-1">Número do Pedido</p>
                                <p class="fw-bold fs-5">#{{ $pedido->numero_pedido }}</p>
                                <hr>
                                <p class="text-muted mb-1">Motivo</p>
                                <p class="text-danger">
                                    {{ session('error', 'Pagamento não aprovado. Tente novamente.') }}
                                </p>
                            </div>
                        </div>

                        <!-- Opções -->
                        <div class="d-flex flex-wrap justify-content-center gap-3 mt-4">
                            <a href="{{ route('checkout.index') }}" 
                               class="btn btn-primary btn-lg">
                                <i class="fas fa-redo me-2"></i> Tentar Novamente
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
        .fail-icon {
            width: 120px;
            height: 120px;
        }
        .rounded-4 {
            border-radius: 1rem !important;
        }
    </style>
    @endpush
</x-app-layout>