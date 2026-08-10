<?php

use App\DataTransferObjects\IncomeData;

it('maps validated data to typed properties and back to an array', function () {
    $data = IncomeData::fromValidated([
        'amount' => '1500',
        'description' => 'Monthly salary',
        'account_id' => '4',
    ]);

    expect($data->amount)->toBe(1500.0)
        ->and($data->description)->toBe('Monthly salary')
        ->and($data->account_id)->toBe(4)
        ->and($data->toArray())->toBe([
            'amount' => 1500.0,
            'description' => 'Monthly salary',
            'account_id' => 4,
        ]);
});
