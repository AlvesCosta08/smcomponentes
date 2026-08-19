{{-- resources/views/checkout/pagamento/pix.blade.php --}}
<x-app-layout>
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <!-- Card Principal -->
                <div class="card shadow-lg border-0 rounded-4">
                    <div class="card-header bg-success text-white rounded-top-4 py-3">
                        <h4 class="mb-0">
                            <i class="fas fa-qrcode me-2"></i> Pagamento PIX
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
                                <p class="fw-bold fs-5 text-success">R$ {{ number_format($pedido->total, 2, ',', '.') }}</p>
                            </div>
                        </div>

                        <hr>

                        <!-- QR Code -->
                        <div class="text-center my-4">
                            <h5 class="mb-3">Escaneie o QR Code abaixo</h5>
                            <div class="d-flex justify-content-center">
                                <div class="qr-container p-3 bg-white rounded-3 shadow-sm" style="max-width: 300px;">
                                    <img src="{{ $pixData['qr_code_base64'] }}" 
                                         alt="QR Code PIX" 
                                         class="img-fluid w-100"
                                         id="qr-code-image">
                                </div>
                            </div>
                        </div>

                        <!-- Código PIX para Copiar -->
                        <div class="mt-4">
                            <label class="fw-bold">Código PIX para copiar:</label>
                            <div class="input-group mt-2">
                                <input type="text" 
                                       class="form-control form-control-lg bg-light" 
                                       id="pix-code" 
                                       value="{{ $pixData['qr_code'] }}" 
                                       readonly>
                                <button class="btn btn-success" type="button" onclick="copyPix()">
                                    <i class="fas fa-copy me-1"></i> Copiar
                                </button>
                            </div>
                            <div id="copy-feedback" class="text-success mt-2 d-none">
                                <i class="fas fa-check-circle"></i> Código copiado com sucesso!
                            </div>
                        </div>

                        <!-- Links Adicionais -->
                        <div class="mt-4 text-center">
                            <a href="{{ $pixData['ticket_url'] }}" 
                               target="_blank" 
                               class="btn btn-outline-success">
                                <i class="fas fa-print me-2"></i> Visualizar Comprovante
                            </a>
                        </div>

                        <!-- Aviso -->
                        <div class="alert alert-info mt-4">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>Importante:</strong> O pagamento será confirmado em até 5 minutos após a transferência.
                        </div>

                        <!-- Botão Acompanhar -->
                        <div class="mt-4 text-center">
                            <a href="{{ route('checkout.sucesso', $pedido) }}" 
                               class="btn btn-success btn-lg px-5">
                                <i class="fas fa-check-circle me-2"></i> Já efetuei o pagamento
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

    @push('scripts')
    <script>
        function copyPix() {
            var input = document.getElementById('pix-code');
            input.select();
            input.setSelectionRange(0, 99999);
            
            try {
                document.execCommand('copy');
                var feedback = document.getElementById('copy-feedback');
                feedback.classList.remove('d-none');
                setTimeout(function() {
                    feedback.classList.add('d-none');
                }, 3000);
            } catch (err) {
                alert('Erro ao copiar: ' + err);
            }
        }

        // Adicionar evento de click no QR Code para ampliar
        document.getElementById('qr-code-image').addEventListener('click', function() {
            this.classList.toggle('img-fluid');
            this.style.maxWidth = this.style.maxWidth === '100%' ? '300px' : '100%';
        });
    </script>
    @endpush

    @push('styles')
    <style>
        .qr-container {
            background: white;
            border-radius: 16px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        .qr-container img {
            transition: all 0.3s ease;
            cursor: pointer;
        }
        .qr-container img:hover {
            transform: scale(1.05);
        }
        .rounded-top-4 {
            border-top-left-radius: 1rem !important;
            border-top-right-radius: 1rem !important;
        }
        .rounded-4 {
            border-radius: 1rem !important;
        }
    </style>
    @endpush
</x-app-layout>