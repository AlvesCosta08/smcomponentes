<?php
// app/Services/PaymentService.php

namespace App\Services;

use App\Models\Pedido;
use Exception;
use Illuminate\Support\Facades\Log;
use MercadoPago\Client\Payment\PaymentClient;
use MercadoPago\Client\Preference\PreferenceClient;
use MercadoPago\MercadoPagoConfig;

class PaymentService
{
    public function __construct()
    {
        // Configurar SDK do Mercado Pago
        MercadoPagoConfig::setAccessToken(config('services.mercadopago.access_token'));
    }

    /**
     * Criar preferência de pagamento para Cartão de Crédito
     */
    public function createPreference(Pedido $pedido): array
    {
        try {
            $client = new PreferenceClient();

            // Itens do pedido
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

            // Criar preferência
            $preference = $client->create([
                'items' => $items,
                'payer' => [
                    'email' => $pedido->user->email,
                    'name' => $pedido->user->name,
                    'phone' => [
                        'number' => $pedido->telefone ?? $pedido->user->telefone ?? '',
                    ],
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
                'payment_methods' => [
                    'excluded_payment_methods' => [],
                    'excluded_payment_types' => [],
                    'installments' => 12,
                ],
            ]);

            Log::info('Preferência criada', [
                'pedido_id' => $pedido->id,
                'preference_id' => $preference->id,
            ]);

            return [
                'success' => true,
                'preference_id' => $preference->id,
                'init_point' => $preference->init_point,
                'sandbox_init_point' => $preference->sandbox_init_point,
            ];

        } catch (Exception $e) {
            Log::error('Erro ao criar preferência: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Gerar QR Code PIX
     */
    public function generatePix(Pedido $pedido): array
    {
        try {
            $client = new PaymentClient();

            $payment = $client->create([
                'transaction_amount' => (float) $pedido->total,
                'description' => "Pedido #{$pedido->numero_pedido} - SM Componentes",
                'payment_method_id' => 'pix',
                'payer' => [
                    'email' => $pedido->user->email,
                ],
                'external_reference' => (string) $pedido->id,
                'notification_url' => config('services.mercadopago.webhook_url'),
            ]);

            Log::info('PIX gerado', [
                'pedido_id' => $pedido->id,
                'payment_id' => $payment->id,
            ]);

            return [
                'success' => true,
                'payment_id' => $payment->id,
                'qr_code' => $payment->point_of_interaction->transaction_data->qr_code,
                'qr_code_base64' => $payment->point_of_interaction->transaction_data->qr_code_base64,
                'ticket_url' => $payment->point_of_interaction->transaction_data->ticket_url,
            ];

        } catch (Exception $e) {
            Log::error('Erro ao gerar PIX: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Gerar Boleto Bancário
     */
    public function generateBoleto(Pedido $pedido): array
    {
        try {
            $client = new PaymentClient();

            $payment = $client->create([
                'transaction_amount' => (float) $pedido->total,
                'description' => "Pedido #{$pedido->numero_pedido} - SM Componentes",
                'payment_method_id' => 'bolbradesco',
                'payer' => [
                    'email' => $pedido->user->email,
                    'first_name' => explode(' ', $pedido->user->name)[0] ?? $pedido->user->name,
                    'last_name' => explode(' ', $pedido->user->name)[1] ?? '',
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

            Log::info('Boleto gerado', [
                'pedido_id' => $pedido->id,
                'payment_id' => $payment->id,
            ]);

            return [
                'success' => true,
                'payment_id' => $payment->id,
                'boleto_url' => $payment->transaction_details->external_resource_url,
                'due_date' => $payment->date_of_expiration,
                'barcode' => $payment->barcode,
            ];

        } catch (Exception $e) {
            Log::error('Erro ao gerar boleto: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Processar webhook de pagamento
     */
    public function processWebhook(array $data): bool
    {
        try {
            // Verificar tipo de notificação
            if ($data['type'] !== 'payment') {
                Log::info('Webhook ignorado: tipo não é payment', ['type' => $data['type'] ?? 'unknown']);
                return false;
            }

            $paymentId = $data['data']['id'] ?? null;
            if (!$paymentId) {
                Log::error('Webhook sem ID de pagamento');
                return false;
            }

            // Buscar pagamento no Mercado Pago
            $client = new PaymentClient();
            $payment = $client->get($paymentId);

            // Buscar pedido pelo external_reference
            $pedido = Pedido::where('id', $payment->external_reference)->first();

            if (!$pedido) {
                Log::error('Pedido não encontrado', ['external_reference' => $payment->external_reference]);
                return false;
            }

            // Atualizar status do pedido baseado no status do pagamento
            $this->updateOrderStatus($pedido, $payment);

            Log::info('Webhook processado', [
                'pedido_id' => $pedido->id,
                'payment_id' => $paymentId,
                'status' => $payment->status,
            ]);

            return true;

        } catch (Exception $e) {
            Log::error('Erro ao processar webhook: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Atualizar status do pedido baseado no pagamento
     */
    protected function updateOrderStatus(Pedido $pedido, $payment): void
    {
        $statusMap = [
            'approved' => 'pago',
            'pending' => 'pendente',
            'rejected' => 'recusado',
            'refunded' => 'cancelado',
            'cancelled' => 'cancelado',
            'in_process' => 'processando',
        ];

        $novoStatus = $statusMap[$payment->status] ?? 'pendente';

        // Atualizar status do pedido
        $pedido->status = $novoStatus;
        $pedido->status_pagamento = $payment->status;
        $pedido->payment_id = $payment->id;

        if ($payment->status === 'approved') {
            $pedido->data_pagamento = now();
        }

        $pedido->save();

        Log::info('Status do pedido atualizado por webhook', [
            'pedido_id' => $pedido->id,
            'novo_status' => $novoStatus,
            'status_pagamento' => $payment->status,
        ]);
    }

    /**
     * Verificar status de pagamento manualmente
     */
    public function checkPaymentStatus(Pedido $pedido): string
    {
        try {
            if (!$pedido->payment_id) {
                return 'pendente';
            }

            $client = new PaymentClient();
            $payment = $client->get($pedido->payment_id);

            return $payment->status;

        } catch (Exception $e) {
            Log::error('Erro ao verificar status do pagamento: ' . $e->getMessage());
            return 'pendente';
        }
    }
}