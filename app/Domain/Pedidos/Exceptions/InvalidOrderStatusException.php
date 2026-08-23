<?php

namespace App\Domain\Pedidos\Exceptions;

use DomainException;

class InvalidOrderStatusException extends DomainException
{
    public function __construct(string $message = "Ação não permitida para o status atual do pedido.")
    {
        parent::__construct($message);
    }
}