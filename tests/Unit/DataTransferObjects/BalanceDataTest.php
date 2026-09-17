<?php

use App\DataTransferObjects\BalanceData;

it('maps validated data to typed properties and back to an array', function () {
    $data = BalanceData::fromValidated([
        'description' => 'Groceries',
        'amount' => '99.99',
        'type' => 'expense',
        'account_name' => 'Checking',
        'account_id' => '7',
    ]);

    expect($data->amount)->toBe(99.99)
        ->and($data->description)->toBe('Groceries')
        ->and($data->account_id)->toBe(7)
        ->and($data->toArray())->toBe([
            'description' => 'Groceries',
            'amount' => 99.99,
            'type' => 'expense',
            'account_name' => 'Checking',
            'account_id' => 7,
            'balanceable_type' => null,
            'balanceable_id' => null,
        ]);
});

it('omits created_at from the array when not provided', function () {
    $data = BalanceData::fromValidated([
        'description' => 'Groceries',
        'amount' => '99.99',
        'type' => 'expense',
        'account_name' => 'Checking',
        'account_id' => '7',
    ]);

    expect($data->created_at)->toBeNull()
        ->and($data->toArray())->not->toHaveKey('created_at');
});

it('parses a provided created_at into the array', function () {
    $data = BalanceData::fromValidated([
        'description' => 'Groceries',
        'amount' => '99.99',
        'type' => 'expense',
        'account_name' => 'Checking',
        'account_id' => '7',
        'created_at' => '2026-06-01T10:00:00+00:00',
    ]);

    expect($data->toArray()['created_at']->toIso8601String())->toBe('2026-06-01T10:00:00+00:00');
});

it('normalizes a non-UTC offset in created_at to UTC', function () {
    $data = BalanceData::fromValidated([
        'description' => 'Groceries',
        'amount' => '99.99',
        'type' => 'expense',
        'account_name' => 'Checking',
        'account_id' => '7',
        'created_at' => '2026-06-01T10:00:00-06:00',
    ]);

    expect($data->toArray()['created_at']->toIso8601String())->toBe('2026-06-01T16:00:00+00:00');
});
