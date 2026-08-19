{{-- resources/views/checkout/pagamento/cartao.blade.php --}}
<x-app-layout>
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <!-- Card Principal -->
                <div class="card shadow-lg border-0 rounded-4">
                    <div class="card-header bg-purple text-white rounded-top-4 py-3">
                        <h4 class="mb-0">
                            <i class="fas fa-credit-card me-2"></i> Pagamento com Cartão
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
                                <p class="fw-bold fs-5 text-purple">R$ {{ number_format($pedido->total, 2, ',', '.') }}</p>
                            </div>
                        </div>

                        <hr>

                        <!-- Checkout Mercado Pago -->
                        <div class="text-center my-4">
                            <h5 class="mb-4">Finalize seu pagamento com cartão de crédito</h5>
                            <p class="text-muted">Você será redirecionado para o ambiente seguro do Mercado Pago</p>
                            
                            <div class="payment-methods my-4">
                                <img src="https://via.placeholder.com/60x40/FFD700/FFFFFF?text=Visa" alt="Visa" class="mx-1">
                                <img src="https://via.placeholder.com/60x40/0066CC/FFFFFF?text=Master" alt="Mastercard" class="mx-1">
                                <img src="https://via.placeholder.com/60x40/FF6600/FFFFFF?text=Elo" alt="Elo" class="mx-1">
                                <img src="https://via.placeholder.com/60x40/0099FF/FFFFFF?text=Hiper" alt="Hipercard" class="mx-1">
                            </div>

                            <div class="alert alert-info">
                                <i class="fas fa-lock me-2"></i>
                                Pagamento seguro com criptografia SSL
                            </div>
                        </div>

                        <!-- Botão Pagar -->
                        <div class="d-grid gap-2 mt-4">
                            <a href="{{ $preference['init_point'] ?? $preference['sandbox_init_point'] ?? '#' }}" 
                               target="_blank"
                               class="btn btn-purple btn-lg">
                                <i class="fas fa-credit-card me-2"></i> Pagar com Cartão de Crédito
                            </a>
                        </div>

                        <!-- Opções Alternativas -->
                        <div class="mt-4 text-center">
                            <p class="text-muted">Ou pague com</p>
                            <div class="d-flex justify-content-center gap-3">
                                <a href="{{ route('checkout.pix', $pedido) }}" class="btn btn-outline-success">
                                    <i class="fas fa-qrcode me-1"></i> PIX
                                </a>
                                <a href="{{ route('checkout.boleto', $pedido) }}" class="btn btn-outline-primary">
                                    <i class="fas fa-barcode me-1"></i> Boleto
                                </a>
                            </div>
                        </div>

                        <hr>

                        <!-- Aviso -->
                        <div class="alert alert-info mt-3">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>Importante:</strong> Após o pagamento, você será redirecionado para a página de confirmação.
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
        .bg-purple {
            background: linear-gradient(135deg, #6C63FF, #4A3FCF);
        }
        .text-purple {
            color: #6C63FF;
        }
        .btn-purple {
            background: linear-gradient(135deg, #6C63FF, #4A3FCF);
            border: none;
            color: white;
            transition: all 0.3s ease;
        }
        .btn-purple:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(108, 99, 255, 0.4);
            color: white;
        }
        .rounded-top-4 {
            border-top-left-radius: 1rem !important;
            border-top-right-radius: 1rem !important;
        }
        .rounded-4 {
            border-radius: 1rem !important;
        }
        .payment-methods img {
            height: 35px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
    </style>
    @endpush
</x-app-layout>