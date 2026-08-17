#!/bin/bash
# setup-payment-fixed.sh

echo "🔧 CONFIGURANDO PAYMENT SERVICE - LOCAL CORRETO"

# 1. Criar diretório Responses
echo "📁 Criando diretório app/DTOs/Responses..."
mkdir -p app/DTOs/Responses

# 2. Criar PaymentResponseDTO
echo "📝 Criando PaymentResponseDTO..."
cat > app/DTOs/Responses/PaymentResponseDTO.php << 'EOF'
<?php
// app/DTOs/Responses/PaymentResponseDTO.php

namespace App\DTOs\Responses;

use App\Models\Pedido;

class PaymentResponseDTO
{
    public function __construct(
        public bool $success,
        public string $payment_id,
        public string $status,
        public Pedido $pedido,
        public ?string $message = null,
        public ?array $extra = null
    ) {}

    public function isApproved(): bool
    {
        return $this->status === 'approved' || $this->status === 'pago';
    }

    public function isPending(): bool
    {
        return $this->status === 'pending' || $this->status === 'pendente';
    }

    public function isRejected(): bool
    {
        return $this->status === 'rejected' || $this->status === 'recusado';
    }

    public function isCancelled(): bool
    {
        return $this->status === 'cancelled' || $this->status === 'cancelado';
    }

    public function toArray(): array
    {
        return [
            'success' => $this->success,
            'payment_id' => $this->payment_id,
            'status' => $this->status,
            'pedido_id' => $this->pedido->id,
            'pedido_numero' => $this->pedido->numero_pedido,
            'pedido_total' => $this->pedido->total,
            'message' => $this->message,
            'extra' => $this->extra,
        ];
    }

    public function toJson(): string
    {
        return json_encode($this->toArray());
    }
}
EOF

# 3. Atualizar PaymentService
echo "📝 Atualizando PaymentService..."
# (Copie o código do PaymentService acima)

# 4. Limpar caches
echo "🧹 Limpando caches..."
docker exec -it smcomponentes_app bash -c "php artisan optimize:clear"
docker exec -it smcomponentes_app bash -c "php artisan config:clear"
docker exec -it smcomponentes_app bash -c "php artisan cache:clear"

# 5. Recarregar autoload
echo "📦 Recarregando autoload..."
docker exec -it smcomponentes_app bash -c "composer dump-autoload"

# 6. Recriar caches
echo "🔄 Recriando caches..."
docker exec -it smcomponentes_app bash -c "php artisan config:cache"
docker exec -it smcomponentes_app bash -c "php artisan route:cache"

# 7. Testar
echo "🧪 Testando..."
docker exec -it smcomponentes_app bash -c "php artisan tinker --execute=\"app(App\\\\Services\\\\Contracts\\\\PaymentServiceInterface::class);\""

echo ""
echo "✅ Payment Service configurado com sucesso!"
echo "📁 DTO criado em: app/DTOs/Responses/PaymentResponseDTO.php"
echo "🌐 Acesse: http://localhost:8080"
echo "💳 Pagamentos em modo MOCK"