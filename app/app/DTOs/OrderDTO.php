<?php
// app/DTOs/OrderDTO.php

namespace App\DTOs;

use Illuminate\Http\Request;

class OrderDTO
{
    public function __construct(
        public readonly string $endereco,
        public readonly string $cidade,
        public readonly string $estado,
        public readonly string $cep,
        public readonly string $forma_pagamento,
        public readonly ?string $telefone = null,
        public readonly ?string $observacoes = null,
        public readonly ?string $numero = null,
        public readonly ?string $complemento = null,
        public readonly ?string $bairro = null,
    ) {}

    /**
     * Criar DTO a partir do Request
     */
    public static function fromRequest(Request $request): self
    {
        return new self(
            endereco: $request->input('endereco'),
            cidade: $request->input('cidade'),
            estado: $request->input('estado'),
            cep: $request->input('cep'),
            forma_pagamento: $request->input('forma_pagamento'),
            telefone: $request->input('telefone'),
            observacoes: $request->input('observacoes'),
            numero: $request->input('numero'),
            complemento: $request->input('complemento'),
            bairro: $request->input('bairro'),
        );
    }

    /**
     * Validar método de pagamento
     */
    public function isValidPaymentMethod(): bool
    {
        return in_array($this->forma_pagamento, ['cartao', 'boleto', 'pix']);
    }

    /**
     * Converter para array
     */
    public function toArray(): array
    {
        return [
            'endereco' => $this->endereco,
            'cidade' => $this->cidade,
            'estado' => $this->estado,
            'cep' => $this->cep,
            'forma_pagamento' => $this->forma_pagamento,
            'telefone' => $this->telefone,
            'observacoes' => $this->observacoes,
            'numero' => $this->numero,
            'complemento' => $this->complemento,
            'bairro' => $this->bairro,
        ];
    }
}