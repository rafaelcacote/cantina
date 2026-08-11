<?php

use App\Support\Phone;

it('formata celular e telefone fixo', function () {
    expect(Phone::format('11999990000'))->toBe('(11) 99999-0000')
        ->and(Phone::format('1133334444'))->toBe('(11) 3333-4444')
        ->and(Phone::isValid('(11) 99999-0000'))->toBeTrue()
        ->and(Phone::isValid('119999'))->toBeFalse()
        ->and(Phone::digits('(11) 99999-0000'))->toBe('11999990000');
});
