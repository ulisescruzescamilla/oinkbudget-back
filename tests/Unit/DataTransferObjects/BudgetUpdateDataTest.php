<?php

use App\DataTransferObjects\BudgetUpdateData;
use App\Enums\PeriodEnum;

it('leaves properties null for fields absent from the validated payload', function () {
    $data = BudgetUpdateData::fromValidated([
        'max_limit' => 2000,
    ]);

    expect($data->max_limit)->toBe(2000.0)
        ->and($data->name)->toBeNull()
        ->and($data->period)->toBeNull()
        ->and($data->is_recurrent)->toBeNull()
        ->and($data->category_id)->toBeNull()
        ->and($data->start_date)->toBeNull();
});

it('maps a fully specified payload', function () {
    $data = BudgetUpdateData::fromValidated([
        'name' => 'Rent',
        'max_limit' => 2000,
        'period' => 'monthly',
        'is_recurrent' => false,
        'percentage_value' => 25,
        'start_date' => '2026-02-01',
        'end_date' => '2026-02-28',
    ]);

    expect($data->name)->toBe('Rent')
        ->and($data->period)->toBe(PeriodEnum::MONTHLY)
        ->and($data->is_recurrent)->toBeFalse();
});

it('omits absent fields from toArray so the repository leaves them untouched', function () {
    $data = BudgetUpdateData::fromValidated([
        'max_limit' => 2000,
        'start_date' => '2026-02-01',
        'end_date' => '2026-02-28',
    ]);

    expect($data->toArray())->toBe([
        'max_limit' => 2000.0,
        'start_date' => '2026-02-01',
        'end_date' => '2026-02-28',
    ]);
});

it('keeps explicit false booleans in toArray instead of dropping them like nulls', function () {
    $data = BudgetUpdateData::fromValidated([
        'is_recurrent' => false,
        'is_active' => false,
    ]);

    expect($data->toArray())->toBe([
        'is_recurrent' => false,
        'is_active' => false,
    ]);
});
