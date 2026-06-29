<?php

use App\DataTransferObjects\ExpenseData;

it('maps validated data to typed properties and back to an array', function () {
    $data = ExpenseData::fromValidated([
        'amount' => '99.99',
        'description' => 'Groceries',
        'budget_id' => '3',
        'account_id' => '7',
    ]);

    expect($data->amount)->toBe(99.99)
        ->and($data->description)->toBe('Groceries')
        ->and($data->budget_id)->toBe(3)
        ->and($data->account_id)->toBe(7)
        ->and($data->toArray())->toBe([
            'amount' => 99.99,
            'description' => 'Groceries',
            'budget_id' => 3,
            'account_id' => 7,
        ]);
});
