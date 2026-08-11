<?php

use App\Support\Cpf;

it('aceita CPF válido e rejeita inválido', function () {
    expect(Cpf::isValid('390.533.447-05'))->toBeTrue()
        ->and(Cpf::isValid('39053344705'))->toBeTrue()
        ->and(Cpf::isValid('12345678901'))->toBeFalse()
        ->and(Cpf::isValid('11111111111'))->toBeFalse()
        ->and(Cpf::isValid('123'))->toBeFalse()
        ->and(Cpf::digits('390.533.447-05'))->toBe('39053344705');
});
