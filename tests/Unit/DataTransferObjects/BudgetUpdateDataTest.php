<?php

use App\DataTransferObjects\BudgetUpdateData;

it('leaves properties null for fields absent from the validated payload', function () {
    $data = BudgetUpdateData::fromValidated([
        'max_limit' => 2000,
    ]);

    expect($data->max_limit)->toBe(2000.0)
        ->and($data->name)->toBeNull()
        ->and($data->is_recurrent)->toBeNull()
        ->and($data->category_id)->toBeNull()
        ->and($data->period_id)->toBeNull();
});

it('maps a fully specified payload', function () {
    $data = BudgetUpdateData::fromValidated([
        'name' => 'Rent',
        'max_limit' => 2000,
        'is_recurrent' => false,
        'percentage_value' => 25,
        'period_id' => 3,
    ]);

    expect($data->name)->toBe('Rent')
        ->and($data->period_id)->toBe(3)
        ->and($data->is_recurrent)->toBeFalse();
});

it('omits absent fields from toArray so the repository leaves them untouched', function () {
    $data = BudgetUpdateData::fromValidated([
        'max_limit' => 2000,
        'period_id' => 3,
    ]);

    expect($data->toArray())->toBe([
        'max_limit' => 2000.0,
        'period_id' => 3,
    ]);
});

it('keeps explicit false booleans in toArray instead of dropping them like nulls', function () {
    $data = BudgetUpdateData::fromValidated([
        'is_recurrent' => false,
    ]);

    expect($data->toArray())->toBe([
        'is_recurrent' => false,
    ]);
});
