<?php

namespace App\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class EncryptedPin implements CastsAttributes
{
    public function get(Model $model, string $key, mixed $value, array $attributes): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        try {
            $decrypted = Crypt::decrypt($value, false);

            return is_string($decrypted) && $decrypted !== '' ? $decrypted : null;
        } catch (DecryptException) {
            // PIN legado em texto puro, ou valor criptografado com outra APP_KEY.
            if (preg_match('/^\d{4,8}$/', (string) $value) === 1) {
                return (string) $value;
            }

            return null;
        }
    }

    public function set(Model $model, string $key, mixed $value, array $attributes): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        return Crypt::encrypt((string) $value, false);
    }
}
