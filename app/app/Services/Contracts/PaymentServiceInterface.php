<?php
// app/Services/Contracts/PaymentServiceInterface.php

namespace App\Services\Contracts;

use App\Models\Pedido;
use App\DTOs\Responses\PaymentResponseDTO;

interface PaymentServiceInterface
{
    public function createPreference(Pedido $pedido): array;
    public function generatePix(Pedido $pedido): array;
    public function generateBoleto(Pedido $pedido): array;
    public function processWebhook(array $data): bool;
    public function checkPaymentStatus(Pedido $pedido): string;
    public function updateOrderStatus(Pedido $pedido, object $payment): void;
    public function refundPayment(Pedido $pedido, ?float $amount = null): bool;
    public function cancelPayment(Pedido $pedido): bool;
    public function isValidPaymentMethod(string $method): bool;
    public function getAvailablePaymentMethods(): array;
    public function processPayment(Pedido $pedido, string $method, array $paymentData = []): PaymentResponseDTO;
}
