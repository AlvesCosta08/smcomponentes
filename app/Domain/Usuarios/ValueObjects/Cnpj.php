<?php

namespace App\Domain\Usuarios\ValueObjects;

use InvalidArgumentException;

final class Cnpj  // Removido "readonly"
{
    private string $number;  // Removido "private" do construtor

    public function __construct(string $number)
    {
        $cleanNumber = preg_replace('/[^0-9]/', '', $number);
        
        if (!$this->isValid($cleanNumber)) {
            throw new InvalidArgumentException('O CNPJ fornecido é inválido.');
        }
        
        $this->number = $cleanNumber;
    }

    public function number(): string
    {
        return $this->number;
    }

    public function formatado(): string
    {
        return preg_replace('/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/', '$1.$2.$3/$4-$5', $this->number);
    }

    public function equals(self $other): bool
    {
        return $this->number === $other->number;
    }

    private function isValid(string $cnpj): bool
    {
        if (strlen($cnpj) !== 14 || preg_match('/(\d)\1{13}/', $cnpj)) {
            return false;
        }

        $pesos1 = [5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];
        $soma1 = 0;
        for ($i = 0; $i < 12; $i++) {
            $soma1 += $cnpj[$i] * $pesos1[$i];
        }
        $digito1 = ($soma1 % 11) < 2 ? 0 : 11 - ($soma1 % 11);

        $pesos2 = [6, 5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];
        $soma2 = 0;
        for ($i = 0; $i < 13; $i++) {
            $soma2 += $cnpj[$i] * $pesos2[$i];
        }
        $digito2 = ($soma2 % 11) < 2 ? 0 : 11 - ($soma2 % 11);

        return $cnpj[12] == $digito1 && $cnpj[13] == $digito2;
    }

    public function __toString(): string
    {
        return $this->number;
    }
}