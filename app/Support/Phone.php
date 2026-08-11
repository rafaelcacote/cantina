<?php

namespace App\Support;

class Phone
{
    public static function digits(?string $value): string
    {
        return preg_replace('/\D+/', '', (string) $value) ?? '';
    }

    public static function isValid(?string $value): bool
    {
        $length = strlen(self::digits($value));

        return $length === 10 || $length === 11;
    }

    public static function format(?string $value): ?string
    {
        $digits = self::digits($value);

        if ($digits === '') {
            return null;
        }

        if (strlen($digits) === 11) {
            return sprintf('(%s) %s-%s', substr($digits, 0, 2), substr($digits, 2, 5), substr($digits, 7));
        }

        if (strlen($digits) === 10) {
            return sprintf('(%s) %s-%s', substr($digits, 0, 2), substr($digits, 2, 4), substr($digits, 6));
        }

        return $value;
    }
}
