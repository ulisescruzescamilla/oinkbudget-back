<?php

use App\DataTransferObjects\AccountData;
use App\Enums\AccountTypeEnum;

it('maps validated data to typed properties and back to an array', function () {
    $data = AccountData::fromValidated([
        'name' => 'Main checking',
        'type' => 'debit_card',
        'amount' => '150.25',
        'hidden' => '0',
    ]);

    expect($data->name)->toBe('Main checking')
        ->and($data->type)->toBe(AccountTypeEnum::DEBIT_CARD)
        ->and($data->amount)->toBe(150.25)
        ->and($data->hidden)->toBeFalse()
        ->and($data->toArray())->toBe([
            'name' => 'Main checking',
            'type' => 'debit_card',
            'amount' => 150.25,
            'hidden' => false,
        ]);
});
