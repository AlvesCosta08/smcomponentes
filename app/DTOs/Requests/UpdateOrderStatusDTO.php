<?php

namespace App\DTOs\Requests;

use App\Domain\Pedidos\Enums\StatusPedidoEnum;
use App\Domain\Pedidos\Enums\StatusPagamentoEnum;
use Illuminate\Http\Request;

final readonly class UpdateOrderStatusDTO
{
    public function __construct(
        public StatusPedidoEnum $status,
        public ?StatusPagamentoEnum $statusPagamento = null,
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            status: StatusPedidoEnum::from($request->input('status')),
            statusPagamento: $request->has('status_pagamento') 
                ? StatusPagamentoEnum::from($request->input('status_pagamento')) 
                : null,
        );
    }
}