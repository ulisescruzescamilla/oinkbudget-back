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

it('maps every account type to its enum case', function (string $type, AccountTypeEnum $expected) {
    $data = AccountData::fromValidated([
        'name' => 'Main checking',
        'type' => $type,
        'amount' => '150.25',
        'hidden' => '0',
    ]);

    expect($data->type)->toBe($expected)
        ->and($data->toArray()['type'])->toBe($type);
})->with([
    'cash' => ['cash', AccountTypeEnum::CASH],
    'debit card' => ['debit_card', AccountTypeEnum::DEBIT_CARD],
    'credit card' => ['credit_card', AccountTypeEnum::CREDIT_CARD],
    'investment' => ['investment', AccountTypeEnum::INVESTMENT],
    'bank' => ['bank', AccountTypeEnum::BANK],
]);

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
