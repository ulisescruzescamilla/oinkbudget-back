<?php

use App\DataTransferObjects\BudgetData;

it('maps validated data to typed properties', function () {
    $data = BudgetData::fromValidated([
        'name' => 'Groceries',
        'max_limit' => '500.5',
        'percentage_value' => '50',
        'category_id' => 1,
    ]);

    expect($data->name)->toBe('Groceries')
        ->and($data->max_limit)->toBe(500.5)
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

it('defaults expense_amount to 0 when absent', function () {
    $data = BudgetData::fromValidated([
        'name' => 'Groceries',
        'max_limit' => 500,
    ]);

    expect($data->expense_amount)->toBe(0.0)
        ->and($data->percentage_value)->toBeNull()
        ->and($data->category_id)->toBeNull();
});

it('ignores a legacy is_recurrent key in the payload', function () {
    $data = BudgetData::fromValidated([
        'name' => 'Groceries',
        'max_limit' => 500,
        'is_recurrent' => true,
    ]);

    expect($data->toArray())->not->toHaveKey('is_recurrent');
});

it('converts back to an array matching Budget::$fillable', function () {
    $data = BudgetData::fromValidated([
        'name' => 'Groceries',
        'max_limit' => 500,
    ]);

    expect($data->toArray())->toBe([
        'name' => 'Groceries',
        'max_limit' => 500.0,
        'expense_amount' => 0.0,
        'percentage_value' => null,
        'category_id' => null,
        'period_id' => null,
        'client_id' => null,
    ]);
});
