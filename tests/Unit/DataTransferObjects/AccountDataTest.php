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
        ->and($data->client_id)->toBeNull()
        ->and($data->toArray())->toBe([
            'name' => 'Main checking',
            'type' => 'debit_card',
            'amount' => 150.25,
            'hidden' => false,
            'client_id' => null,
        ]);
});

it('maps a provided client_id', function () {
    $data = AccountData::fromValidated([
        'name' => 'Main checking',
        'type' => 'debit_card',
        'amount' => '150.25',
        'hidden' => '0',
        'client_id' => '9f6a6f2e-1c9a-4c2e-8f0a-1a2b3c4d5e6f',
    ]);

    expect($data->client_id)->toBe('9f6a6f2e-1c9a-4c2e-8f0a-1a2b3c4d5e6f')
        ->and($data->toArray()['client_id'])->toBe('9f6a6f2e-1c9a-4c2e-8f0a-1a2b3c4d5e6f');
});
