<?php
// app/Exceptions/OutOfStockException.php

namespace App\Exceptions;

use Exception;

class OutOfStockException extends Exception
{
    public function __construct(string $message = "Produto sem estoque disponível")
    {
        parent::__construct($message);
    }

    /**
     * Renderizar a exceção
     */
    public function render($request)
    {
        if ($request->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => $this->getMessage(),
                'error' => 'out_of_stock'
            ], 422);
        }

        return back()->withInput()->with('error', $this->getMessage());
    }
}