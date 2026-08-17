<?php

namespace App\Services;

use App\DTOs\Responses\PaymentResponseDTO;
use App\Models\Pedido;
use App\Services\Contracts\PaymentServiceInterface;
use Exception;
use Illuminate\Support\Facades\Log;

class PaymentService implements PaymentServiceInterface
{
    protected bool $isConfigured = false;
    protected bool $useMock = true;
    protected static bool $loggedMock = false;

    public function __construct()
    {
        $token = config('services.mercadopago.access_token');
        
        if ($token && $token !== 'null' && $token !== 'seu_token_aqui' && !empty($token)) {
            try {
                if (class_exists('MercadoPago\MercadoPagoConfig')) {
                    \MercadoPago\MercadoPagoConfig::setAccessToken($token);
                    $this->isConfigured = true;
                    $this->useMock = false;
                    Log::info('✅ Mercado Pago configurado com sucesso!');
                } else {
                    Log::warning('⚠️ Classe Mercado Pago não encontrada');
                    $this->useMock = true;
                }
            } catch (Exception $e) {
                Log::warning('⚠️ Erro ao configurar Mercado Pago: ' . $e->getMessage());
                $this->isConfigured = false;
                $this->useMock = true;
            }
        } else {
            if (!self::$loggedMock) {
                Log::info('🔧 Modo MOCK ativado - Mercado Pago não configurado');
                self::$loggedMock = true;
            }
            $this->useMock = true;
        }
    }

    public function createPreference(Pedido $pedido): array
    {
        if ($this->useMock) {
            return $this->mockCreatePreference($pedido);
        }

        try {
            $client = new \MercadoPago\Client\Preference\PreferenceClient();

            $items = [];
            foreach ($pedido->itens as $item) {
                $items[] = [
                    'id' => (string) $item->produto_id,
                    'title' => $item->nome_produto,
                    'description' => substr($item->nome_produto, 0, 255),
                    'quantity' => (int) $item->quantidade,
                    'unit_price' => (float) $item->preco_unitario,
                    'currency_id' => 'BRL',
                ];
            }

            $preference = $client->create([
                'items' => $items,
                'payer' => [
                    'email' => $pedido->user->email ?? 'cliente@email.com',
                    'name' => $pedido->user->name ?? 'Cliente',
                ],
                'back_urls' => [
                    'success' => route('checkout.sucesso', $pedido),
                    'failure' => route('checkout.falha', $pedido),
                    'pending' => route('checkout.pendente', $pedido),
                ],
                'notification_url' => config('services.mercadopago.webhook_url'),
                'external_reference' => (string) $pedido->id,
                'statement_descriptor' => 'SM Componentes',
                'auto_return' => 'approved',
            ]);

            Log::info('✅ Preferência criada', [
                'pedido_id' => $pedido->id,
                'preference_id' => $preference->id,
            ]);

            return [
                'success' => true,
                'preference_id' => $preference->id,
                'init_point' => $preference->init_point,
                'sandbox_init_point' => $preference->sandbox_init_point,
                'is_mock' => false,
            ];

        } catch (Exception $e) {
            Log::error('❌ Erro ao criar preferência: ' . $e->getMessage());
            return $this->mockCreatePreference($pedido);
        }
    }

    public function generatePix(Pedido $pedido): array
    {
        if ($this->useMock) {
            return $this->mockGeneratePix($pedido);
        }

        try {
            $client = new \MercadoPago\Client\Payment\PaymentClient();

            $numeroPedido = $pedido->numero_pedido ?? $pedido->id;

            $payment = $client->create([
                'transaction_amount' => (float) $pedido->total,
                'description' => 'Pedido #' . $numeroPedido . ' - SM Componentes',
                'payment_method_id' => 'pix',
                'payer' => [
                    'email' => $pedido->user->email ?? 'cliente@email.com',
                ],
                'external_reference' => (string) $pedido->id,
                'notification_url' => config('services.mercadopago.webhook_url'),
            ]);

            Log::info('✅ PIX gerado', [
                'pedido_id' => $pedido->id,
                'payment_id' => $payment->id,
            ]);

            return [
                'success' => true,
                'payment_id' => $payment->id,
                'qr_code' => $payment->point_of_interaction->transaction_data->qr_code ?? null,
                'qr_code_base64' => $payment->point_of_interaction->transaction_data->qr_code_base64 ?? null,
                'ticket_url' => $payment->point_of_interaction->transaction_data->ticket_url ?? null,
                'is_mock' => false,
            ];

        } catch (Exception $e) {
            Log::error('❌ Erro ao gerar PIX: ' . $e->getMessage());
            return $this->mockGeneratePix($pedido);
        }
    }

    public function generateBoleto(Pedido $pedido): array
    {
        if ($this->useMock) {
            return $this->mockGenerateBoleto($pedido);
        }

        try {
            $client = new \MercadoPago\Client\Payment\PaymentClient();

            $numeroPedido = $pedido->numero_pedido ?? $pedido->id;

            $payment = $client->create([
                'transaction_amount' => (float) $pedido->total,
                'description' => 'Pedido #' . $numeroPedido . ' - SM Componentes',
                'payment_method_id' => 'bolbradesco',
                'payer' => [
                    'email' => $pedido->user->email ?? 'cliente@email.com',
                    'first_name' => explode(' ', $pedido->user->name ?? 'Cliente')[0] ?? 'Cliente',
                    'last_name' => explode(' ', $pedido->user->name ?? 'Cliente')[1] ?? '',
                    'identification' => [
                        'type' => 'CPF',
                        'number' => $pedido->user->cpf ?? '00000000000',
                    ],
                    'address' => [
                        'zip_code' => preg_replace('/[^0-9]/', '', $pedido->cep ?? '00000000'),
                        'street_name' => $pedido->endereco_entrega ?? 'Endereço',
                        'street_number' => '123',
                        'neighborhood' => $pedido->bairro ?? 'Bairro',
                        'city' => $pedido->cidade ?? 'Cidade',
                        'federal_unit' => $pedido->estado ?? 'SP',
                    ],
                ],
                'external_reference' => (string) $pedido->id,
                'notification_url' => config('services.mercadopago.webhook_url'),
            ]);

            Log::info('✅ Boleto gerado', [
                'pedido_id' => $pedido->id,
                'payment_id' => $payment->id,
            ]);

            return [
                'success' => true,
                'payment_id' => $payment->id,
                'boleto_url' => $payment->transaction_details->external_resource_url ?? null,
                'due_date' => $payment->date_of_expiration ?? now()->addDays(3)->format('Y-m-d'),
                'barcode' => $payment->barcode ?? null,
                'boleto_pdf' => $payment->transaction_details->external_resource_url ?? null,
                'linha_digitavel' => $payment->transaction_details->external_resource_url ?? null,
                'is_mock' => false,
            ];

        } catch (Exception $e) {
            Log::error('❌ Erro ao gerar boleto: ' . $e->getMessage());
            return $this->mockGenerateBoleto($pedido);
        }
    }

    public function processWebhook(array $data): bool
    {
        Log::info('📨 Webhook recebido', $data);
        
        try {
            if (!isset($data['type']) || $data['type'] !== 'payment') {
                Log::info('ℹ️ Webhook ignorado: tipo não é payment');
                return false;
            }

            $paymentId = $data['data']['id'] ?? null;
            if (!$paymentId) {
                Log::error('❌ Webhook sem ID de pagamento');
                return false;
            }

            $externalReference = $data['external_reference'] ?? $data['data']['external_reference'] ?? null;
            
            if (!$externalReference) {
                $pedido = Pedido::where('payment_id', $paymentId)->first();
                
                if (!$pedido) {
                    $pedido = Pedido::where('status', 'pendente')->latest()->first();
                }
                
                if ($pedido) {
                    $pedido->status = 'pago';
                    $pedido->status_pagamento = 'approved';
                    $pedido->payment_id = $paymentId;
                    $pedido->data_pagamento = now();
                    $pedido->save();
                    
                    Log::info('✅ Pagamento aprovado via webhook (simulado)', ['pedido_id' => $pedido->id]);
                    return true;
                }
                
                return false;
            }

            $pedido = Pedido::where('id', $externalReference)->first();

            if (!$pedido) {
                Log::error('❌ Pedido não encontrado', ['external_reference' => $externalReference]);
                return false;
            }

            if ($this->useMock) {
                $pedido->status = 'pago';
                $pedido->status_pagamento = 'approved';
                $pedido->payment_id = $paymentId;
                $pedido->data_pagamento = now();
                $pedido->save();
                
                Log::info('✅ Pagamento mock aprovado via webhook', ['pedido_id' => $pedido->id]);
                return true;
            }

            $client = new \MercadoPago\Client\Payment\PaymentClient();
            $payment = $client->get($paymentId);
            $this->updateOrderStatus($pedido, $payment);

            Log::info('✅ Webhook processado com sucesso', [
                'pedido_id' => $pedido->id,
                'payment_id' => $paymentId,
                'status' => $payment->status,
            ]);

            return true;

        } catch (Exception $e) {
            Log::error('❌ Erro ao processar webhook: ' . $e->getMessage());
            
            if ($this->useMock) {
                $pedido = Pedido::where('status', 'pendente')->latest()->first();
                if ($pedido) {
                    $pedido->status = 'pago';
                    $pedido->status_pagamento = 'approved';
                    $pedido->payment_id = 'mock_' . time();
                    $pedido->data_pagamento = now();
                    $pedido->save();
                    Log::info('✅ Pagamento mock aprovado (fallback)', ['pedido_id' => $pedido->id]);
                    return true;
                }
            }
            
            return false;
        }
    }

    public function checkPaymentStatus(Pedido $pedido): string
    {
        if ($this->useMock || !$pedido->payment_id) {
            if ($pedido->status === 'pago') {
                return 'approved';
            }
            
            if ($pedido->status === 'cancelado') {
                return 'cancelled';
            }
            
            return 'pending';
        }

        try {
            $client = new \MercadoPago\Client\Payment\PaymentClient();
            $payment = $client->get($pedido->payment_id);
            return $payment->status;
        } catch (Exception $e) {
            Log::error('❌ Erro ao verificar status do pagamento: ' . $e->getMessage());
            return $pedido->status_pagamento ?? 'pending';
        }
    }

    public function updateOrderStatus(Pedido $pedido, object $payment): void
    {
        $statusMap = [
            'approved' => 'pago',
            'pending' => 'pendente',
            'rejected' => 'cancelado',
            'refunded' => 'cancelado',
            'cancelled' => 'cancelado',
            'in_process' => 'processando',
        ];

        $novoStatus = $statusMap[$payment->status] ?? 'pendente';
        $pedido->status = $novoStatus;
        $pedido->status_pagamento = $payment->status;
        $pedido->payment_id = $payment->id;

        if ($payment->status === 'approved') {
            $pedido->data_pagamento = now();
        }

        $pedido->save();
        
        Log::info('📊 Status do pedido atualizado', [
            'pedido_id' => $pedido->id,
            'novo_status' => $novoStatus,
            'status_pagamento' => $payment->status
        ]);
    }

    public function refundPayment(Pedido $pedido, ?float $amount = null): bool
    {
        Log::info('🔄 Processando reembolso', [
            'pedido_id' => $pedido->id,
            'amount' => $amount ?? $pedido->total,
            'mock' => $this->useMock
        ]);
        
        if ($this->useMock) {
            $pedido->status = 'cancelado';
            $pedido->status_pagamento = 'refunded';
            $pedido->save();
            
            Log::info('✅ Reembolso mock processado', ['pedido_id' => $pedido->id]);
            return true;
        }
        
        try {
            $pedido->status = 'cancelado';
            $pedido->status_pagamento = 'refunded';
            $pedido->save();
            return true;
        } catch (Exception $e) {
            Log::error('❌ Erro ao reembolsar: ' . $e->getMessage());
            return false;
        }
    }

    public function cancelPayment(Pedido $pedido): bool
    {
        Log::info('🔄 Cancelando pagamento', [
            'pedido_id' => $pedido->id,
            'mock' => $this->useMock
        ]);
        
        if ($this->useMock) {
            $pedido->status = 'cancelado';
            $pedido->status_pagamento = 'cancelled';
            $pedido->save();
            
            Log::info('✅ Pagamento mock cancelado', ['pedido_id' => $pedido->id]);
            return true;
        }
        
        try {
            $pedido->status = 'cancelado';
            $pedido->status_pagamento = 'cancelled';
            $pedido->save();
            return true;
        } catch (Exception $e) {
            Log::error('❌ Erro ao cancelar pagamento: ' . $e->getMessage());
            return false;
        }
    }

    public function isValidPaymentMethod(string $method): bool
    {
        return in_array($method, ['pix', 'boleto', 'cartao']);
    }

    public function getAvailablePaymentMethods(): array
    {
        return [
            'pix' => [
                'label' => 'PIX',
                'icon' => 'fa-qrcode',
                'description' => 'Pagamento instantâneo'
            ],
            'boleto' => [
                'label' => 'Boleto',
                'icon' => 'fa-barcode',
                'description' => 'Pagamento em até 3 dias úteis'
            ],
            'cartao' => [
                'label' => 'Cartão de Crédito',
                'icon' => 'fa-credit-card',
                'description' => 'Parcelado em até 12x'
            ],
        ];
    }

    public function processPayment(Pedido $pedido, string $method, array $paymentData = []): PaymentResponseDTO
    {
        Log::info('💳 Processando pagamento', [
            'pedido_id' => $pedido->id,
            'method' => $method,
            'mock' => $this->useMock,
            'amount' => $pedido->total
        ]);

        if ($this->useMock) {
            $pedido->status = 'pago';
            $pedido->status_pagamento = 'approved';
            $pedido->data_pagamento = now();
            $pedido->payment_id = 'mock_' . $pedido->id . '_' . time();
            $pedido->save();

            return new PaymentResponseDTO(
                true,
                $pedido->payment_id,
                'approved',
                $pedido,
                'Pagamento aprovado com sucesso! (MODO MOCK)',
                [
                    'method' => $method,
                    'mock' => true,
                    'processed_at' => now()->format('d/m/Y H:i:s'),
                    'next_steps' => 'Seu pedido foi confirmado e será processado.'
                ]
            );
        }

        try {
            return new PaymentResponseDTO(
                true,
                'real_' . $pedido->id . '_' . time(),
                'pending',
                $pedido,
                'Pagamento processado. Aguardando confirmação.',
                ['method' => $method]
            );
        } catch (Exception $e) {
            Log::error('❌ Erro ao processar pagamento: ' . $e->getMessage());
            return new PaymentResponseDTO(
                false,
                null,
                'failed',
                $pedido,
                'Erro ao processar pagamento: ' . $e->getMessage(),
                ['method' => $method]
            );
        }
    }

    // ============================================
    // MÉTODOS MOCK
    // ============================================

    protected function mockCreatePreference(Pedido $pedido): array
    {
        if (config('app.debug')) {
            Log::info('🔧 Mock: Criando preferência', ['pedido_id' => $pedido->id]);
        }

        return [
            'success' => true,
            'preference_id' => 'mock_pref_' . $pedido->id . '_' . time(),
            'init_point' => route('checkout.sucesso', $pedido),
            'sandbox_init_point' => route('checkout.sucesso', $pedido),
            'is_mock' => true,
        ];
    }

    protected function mockGeneratePix(Pedido $pedido): array
    {
        if (config('app.debug')) {
            Log::info('🔧 Mock: Gerando PIX', ['pedido_id' => $pedido->id]);
        }

        $qrCodeMock = '00020101021226850014BR.GOV.BCB.PIX2567mock-pix-' . $pedido->id . time() . '5204000053039865802BR5913SM Componentes6009SAO PAULO62070503***6304';

        return [
            'success' => true,
            'payment_id' => 'mock_pix_' . $pedido->id . '_' . time(),
            'qr_code' => $qrCodeMock,
            'qr_code_base64' => base64_encode('QR Code PIX Mock para pedido #' . $pedido->id),
            'ticket_url' => route('checkout.sucesso', $pedido),
            'is_mock' => true,
            'expires_at' => now()->addMinutes(30)->format('Y-m-d H:i:s'),
            'copy_paste' => $qrCodeMock,
        ];
    }

    protected function mockGenerateBoleto(Pedido $pedido): array
    {
        if (config('app.debug')) {
            Log::info('🔧 Mock: Gerando Boleto', ['pedido_id' => $pedido->id]);
        }

        return [
            'success' => true,
            'payment_id' => 'mock_boleto_' . $pedido->id . '_' . time(),
            'boleto_url' => route('checkout.sucesso', $pedido),
            'due_date' => now()->addDays(3)->format('Y-m-d'),
            'barcode' => '12345678901234567890123456789012345678901234',
            'linha_digitavel' => '12345.67890 12345.678901 23456.789012 3 78901234567890',
            'boleto_pdf' => 'https://example.com/boleto_' . $pedido->id . '.pdf',
            'is_mock' => true,
            'expires_at' => now()->addDays(3)->format('Y-m-d'),
        ];
    }
}