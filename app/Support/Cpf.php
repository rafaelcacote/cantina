<?php

namespace App\Support;

class Cpf
{
    public static function digits(?string $value): string
    {
        return preg_replace('/\D+/', '', (string) $value) ?? '';
    }

    public static function isValid(?string $value): bool
    {
        $cpf = self::digits($value);

        if (strlen($cpf) !== 11) {
            return false;
        }

        if (preg_match('/^(\d)\1{10}$/', $cpf)) {
            return false;
        }

        return self::checkDigit($cpf, 9) === (int) $cpf[9]
            && self::checkDigit($cpf, 10) === (int) $cpf[10];
    }

    public static function format(?string $value): ?string
    {
        $cpf = self::digits($value);

        if (strlen($cpf) !== 11) {
            return $value;
        }

        return sprintf('%s.%s.%s-%s', substr($cpf, 0, 3), substr($cpf, 3, 3), substr($cpf, 6, 3), substr($cpf, 9, 2));
    }

    private static function checkDigit(string $cpf, int $length): int
    {
        $sum = 0;

        for ($index = 0; $index < $length; $index++) {
            $sum += (int) $cpf[$index] * (($length + 1) - $index);
        }

        $remainder = ($sum * 10) % 11;

        return $remainder === 10 ? 0 : $remainder;
    }
}
