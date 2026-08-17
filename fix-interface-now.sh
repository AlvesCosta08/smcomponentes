#!/bin/bash
# fix-interface-now.sh

echo "🔧 CORRIGINDO INTERFACE - LOCAL CORRETO"

# 1. Atualizar a interface
echo "📝 Atualizando PaymentServiceInterface..."
cat > app/Services/Contracts/PaymentServiceInterface.php << 'EOF'
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
EOF

# 2. Limpar caches
echo "🧹 Limpando caches..."
docker exec -it smcomponentes_app bash -c "php artisan optimize:clear"
docker exec -it smcomponentes_app bash -c "php artisan config:clear"
docker exec -it smcomponentes_app bash -c "php artisan cache:clear"

# 3. Recarregar autoload
echo "📦 Recarregando autoload..."
docker exec -it smcomponentes_app bash -c "composer dump-autoload"

# 4. Recriar caches
echo "🔄 Recriando caches..."
docker exec -it smcomponentes_app bash -c "php artisan config:cache"
docker exec -it smcomponentes_app bash -c "php artisan route:cache"

# 5. Testar
echo "🧪 Testando PaymentService..."
docker exec -it smcomponentes_app bash -c "php artisan tinker --execute=\"app(App\\\\Services\\\\Contracts\\\\PaymentServiceInterface::class);\""

echo ""
echo "✅ Interface corrigida!"
echo "🌐 Acesse: http://localhost:8080"