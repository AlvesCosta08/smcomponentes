<?php

namespace App\Domain\Pedidos\Exceptions;

use DomainException;

class PedidoNaoPodeSerCanceladoException extends DomainException
{
    public function __construct(string $message = "O pedido não pode ser cancelado no status atual.")
    {
        parent::__construct($message);
    }
}