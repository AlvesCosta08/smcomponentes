<?php

namespace App\Domain\Usuarios\ValueObjects;

use InvalidArgumentException;

final readonly class Cpf
{
    public function __construct(
        private string $number
    ) {
        $this->number = preg_replace('/[^0-9]/', '', $this->number);
        
        if (!$this->isValid($this->number)) {
            throw new InvalidArgumentException('O CPF fornecido é inválido.');
        }
    }

    public function number(): string
    {
        return $this->number;
    }

    public function formatado(): string
    {
        return preg_replace('/(\d{3})(\d{3})(\d{3})(\d{2})/', '$1.$2.$3-$4', $this->number);
    }

    public function equals(self $other): bool
    {
        return $this->number === $other->number;
    }

    private function isValid(string $cpf): bool
    {
        if (strlen($cpf) !== 11 || preg_match('/(\d)\1{10}/', $cpf)) {
            return false;
        }

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
}