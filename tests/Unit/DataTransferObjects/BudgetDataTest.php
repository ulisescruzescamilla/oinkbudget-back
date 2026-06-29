<?php

use App\DataTransferObjects\BudgetData;
use App\Enums\PeriodEnum;

it('maps validated data to typed properties', function () {
    $data = BudgetData::fromValidated([
        'name' => 'Groceries',
        'max_limit' => '500.5',
        'period' => 'monthly',
        'is_recurrent' => true,
        'percentage_value' => '50',
        'start_date' => '2026-01-01',
        'end_date' => '2026-01-31',
    ]);

    expect($data->name)->toBe('Groceries')
        ->and($data->max_limit)->toBe(500.5)
        ->and($data->period)->toBe(PeriodEnum::MONTHLY)
        ->and($data->is_recurrent)->toBeTrue()
        ->and($data->is_active)->toBeTrue()
        ->and($data->expense_amount)->toBe(0.0)
        ->and($data->percentage_value)->toBe(50)
        ->and($data->start_date)->toBe('2026-01-01')
        ->and($data->end_date)->toBe('2026-01-31');
});

it('defaults expense_amount to 0 and is_recurrent to false when absent', function () {
    $data = BudgetData::fromValidated([
        'name' => 'Groceries',
        'max_limit' => 500,
        'period' => 'weekly',
        'start_date' => '2026-01-01',
        'end_date' => '2026-01-07',
    ]);

    expect($data->expense_amount)->toBe(0.0)
        ->and($data->is_recurrent)->toBeFalse()
        ->and($data->percentage_value)->toBeNull();
});

it('converts back to an array matching Budget::$fillable', function () {
    $data = BudgetData::fromValidated([
        'name' => 'Groceries',
        'max_limit' => 500,
        'period' => 'yearly',
        'start_date' => '2026-01-01',
        'end_date' => '2026-12-31',
    ]);

    expect($data->toArray())->toBe([
        'name' => 'Groceries',
        'max_limit' => 500.0,
        'period' => 'yearly',
        'is_recurrent' => false,
        'is_active' => true,
        'expense_amount' => 0.0,
        'percentage_value' => null,
        'start_date' => '2026-01-01',
        'end_date' => '2026-12-31',
    ]);
});
