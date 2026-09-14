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
        ->and($data->client_id)->toBeNull()
        ->and($data->toArray())->toBe([
            'amount' => 99.99,
            'description' => 'Groceries',
            'budget_id' => 3,
            'account_id' => 7,
            'client_id' => null,
        ]);
});

it('maps a provided client_id', function () {
    $data = ExpenseData::fromValidated([
        'amount' => '99.99',
        'description' => 'Groceries',
        'budget_id' => '3',
        'account_id' => '7',
        'client_id' => '9f6a6f2e-1c9a-4c2e-8f0a-1a2b3c4d5e6f',
    ]);

    expect($data->client_id)->toBe('9f6a6f2e-1c9a-4c2e-8f0a-1a2b3c4d5e6f')
        ->and($data->toArray()['client_id'])->toBe('9f6a6f2e-1c9a-4c2e-8f0a-1a2b3c4d5e6f');
});

it('omits created_at from the array when not provided', function () {
    $data = ExpenseData::fromValidated([
        'amount' => '99.99',
        'description' => 'Groceries',
        'budget_id' => '3',
        'account_id' => '7',
    ]);

    expect($data->created_at)->toBeNull()
        ->and($data->toArray())->not->toHaveKey('created_at');
});

it('parses a provided created_at into the array', function () {
    $data = ExpenseData::fromValidated([
        'amount' => '99.99',
        'description' => 'Groceries',
        'budget_id' => '3',
        'account_id' => '7',
        'created_at' => '2026-06-01T10:00:00+00:00',
    ]);

    expect($data->created_at)->toBe('2026-06-01T10:00:00+00:00')
        ->and($data->toArray()['created_at']->toIso8601String())->toBe('2026-06-01T10:00:00+00:00');
});
