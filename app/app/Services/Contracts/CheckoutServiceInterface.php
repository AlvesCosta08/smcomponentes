<?php
// app/Services/Contracts/CheckoutServiceInterface.php

namespace App\Services\Contracts;

use App\DTOs\CheckoutDTO;
use App\DTOs\Responses\CheckoutResponseDTO;

interface CheckoutServiceInterface
{
    /**
     * Iniciar processo de checkout
     *
     * @param CheckoutDTO $dto Dados do checkout
     * @return CheckoutResponseDTO
     * @throws \App\Exceptions\CheckoutValidationException
     */
    public function initiateCheckout(CheckoutDTO $dto): CheckoutResponseDTO;

    /**
     * Validar dados do checkout
     *
     * @param CheckoutDTO $dto
     * @throws \App\Exceptions\CheckoutValidationException
     */
    public function validateCheckout(CheckoutDTO $dto): void;

    /**
     * Validar carrinho antes do checkout
     *
     * @param array $carrinho
     * @throws \App\Exceptions\OutOfStockException
     */
    public function validateCart(array $carrinho): void;

    /**
     * Calcular totais do checkout
     *
     * @param array $carrinho
     * @param string|null $cupom
     * @return array ['subtotal' => float, 'desconto' => float, 'frete' => float, 'total' => float]
     */
    public function calculateTotals(array $carrinho, ?string $cupom = null): array;

    /**
     * Aplicar cupom de desconto
     *
     * @param string $codigo
     * @param float $subtotal
     * @return array ['desconto' => float, 'mensagem' => string]
     */
    public function applyCoupon(string $codigo, float $subtotal): array;

    /**
     * Remover cupom de desconto
     *
     * @return void
     */
    public function removeCoupon(): void;

    /**
     * Calcular frete
     *
     * @param string $cep
     * @param array $itens
     * @return float
     */
    public function calculateShipping(string $cep, array $itens): float;

    /**
     * Verificar se checkout é válido
     *
     * @param array $carrinho
     * @return bool
     */
    public function isValidCheckout(array $carrinho): bool;

    /**
     * Preparar dados para pagamento
     *
     * @param CheckoutDTO $dto
     * @param array $carrinho
     * @return array
     */
    public function preparePaymentData(CheckoutDTO $dto, array $carrinho): array;

    /**
     * Finalizar checkout após pagamento
     *
     * @param int $orderId
     * @param string $paymentId
     * @param string $status
     * @return CheckoutResponseDTO
     */
    public function finalizeCheckout(int $orderId, string $paymentId, string $status): CheckoutResponseDTO;
}