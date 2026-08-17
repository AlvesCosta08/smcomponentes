<?php
// app/Http/Controllers/Api/WebhookController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    public function __construct(
        protected PaymentService $paymentService
    ) {}

    /**
     * Receber webhook do Mercado Pago
     */
    public function mercadopago(Request $request): Response
    {
        try {
            $data = $request->all();
            
            Log::info('📥 Webhook recebido do Mercado Pago', [
                'data' => $data,
                'ip' => $request->ip(),
                'headers' => $request->headers->all()
            ]);

            // Verificar se é uma notificação válida
            if (!isset($data['type']) || $data['type'] !== 'payment') {
                Log::info('Webhook ignorado: tipo não é payment', [
                    'type' => $data['type'] ?? 'unknown'
                ]);
                return response('OK - Ignorado', 200);
            }

            // Processar webhook
            $success = $this->paymentService->processWebhook($data);

            if ($success) {
                Log::info('✅ Webhook processado com sucesso');
                return response('OK', 200);
            }

            Log::warning('⚠️ Webhook processado mas nenhuma ação tomada');
            return response('Webhook processed but no action taken', 200);

        } catch (\Exception $e) {
            Log::error('❌ Erro ao processar webhook: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            
            return response('Error: ' . $e->getMessage(), 500);
        }
    }
}