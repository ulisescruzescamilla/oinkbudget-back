<?php

use App\DataTransferObjects\BudgetData;

it('maps validated data to typed properties', function () {
    $data = BudgetData::fromValidated([
        'name' => 'Groceries',
        'max_limit' => '500.5',
        'is_recurrent' => true,
        'percentage_value' => '50',
        'category_id' => 1,
    ]);

    expect($data->name)->toBe('Groceries')
        ->and($data->max_limit)->toBe(500.5)
        ->and($data->is_recurrent)->toBeTrue()
        ->and($data->expense_amount)->toBe(0.0)
        ->and($data->percentage_value)->toBe(50)
        ->and($data->category_id)->toBe(1)
        ->and($data->client_id)->toBeNull();
});

it('maps a provided client_id', function () {
    $data = BudgetData::fromValidated([
        'name' => 'Groceries',
        'max_limit' => 500,
        'client_id' => '9f6a6f2e-1c9a-4c2e-8f0a-1a2b3c4d5e6f',
    ]);

    expect($data->client_id)->toBe('9f6a6f2e-1c9a-4c2e-8f0a-1a2b3c4d5e6f')
        ->and($data->toArray()['client_id'])->toBe('9f6a6f2e-1c9a-4c2e-8f0a-1a2b3c4d5e6f');
});

it('defaults expense_amount to 0 and is_recurrent to false when absent', function () {
    $data = BudgetData::fromValidated([
        'name' => 'Groceries',
        'max_limit' => 500,
    ]);

    expect($data->expense_amount)->toBe(0.0)
        ->and($data->is_recurrent)->toBeFalse()
        ->and($data->percentage_value)->toBeNull()
        ->and($data->category_id)->toBeNull();
});

it('converts back to an array matching Budget::$fillable', function () {
    $data = BudgetData::fromValidated([
        'name' => 'Groceries',
        'max_limit' => 500,
    ]);

    expect($data->toArray())->toBe([
        'name' => 'Groceries',
        'max_limit' => 500.0,
        'is_recurrent' => false,
        'expense_amount' => 0.0,
        'percentage_value' => null,
        'category_id' => null,
        'period_id' => null,
        'client_id' => null,
    ]);
});
