<?php

namespace App\Domain\Usuarios\ValueObjects;

use InvalidArgumentException;

class Cpf
{
    private string $number;

    public function __construct(string $number)
    {
        $cleanNumber = preg_replace('/[^0-9]/', '', $number);
        
        if (!$this->validate($cleanNumber)) {
            throw new InvalidArgumentException("CPF inválido: {$number}");
        }
        
        $this->number = $cleanNumber;
    }

    public function number(): string
    {
        return $this->number;
    }

    public function formatado(): string
    {
        return substr($this->number, 0, 3) . '.' .
               substr($this->number, 3, 3) . '.' .
               substr($this->number, 6, 3) . '-' .
               substr($this->number, 9, 2);
    }

    private function validate(string $cpf): bool
    {
        // Verifica se tem 11 dígitos
        if (strlen($cpf) !== 11) {
            return false;
        }

        // Verifica se todos os dígitos são iguais
        if (preg_match('/(\d)\1{10}/', $cpf)) {
            return false;
        }

        // Calcula os dígitos verificadores
        for ($t = 9; $t < 11; $t++) {
            for ($d = 0, $c = 0; $c < $t; $c++) {
                $d += $cpf[$c] * (($t + 1) - $c);
            }
            $d = ((10 * $d) % 11) % 10;
            if ($cpf[$c] != $d) {
                return false;
            }
        }

        return true;
    }

    public function __toString(): string
    {
        return $this->number;
    }
}
