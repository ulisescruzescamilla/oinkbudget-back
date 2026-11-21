<?php

use App\DataTransferObjects\BudgetUpdateData;
use App\Models\Account;
use App\Models\Budget;
use App\Models\Expense;
use App\Models\Period;
use App\Repositories\BudgetRepository;
use App\Repositories\ExpenseRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('only changes the fields present in a partial update', function () {
    Account::factory()->create(['amount' => 1000, 'hidden' => false]);

    $budget = Budget::factory()->create([
        'name' => 'Groceries',
        'max_limit' => 100,
        'percentage_value' => 10,
        'expense_amount' => 42,
    ]);

    $updated = app(BudgetRepository::class)->update(
        $budget,
        BudgetUpdateData::fromValidated(['max_limit' => 250]),
    );

    expect($updated->name)->toBe('Groceries')
        ->and((float) $updated->expense_amount)->toBe(42.0)
        ->and((float) $updated->max_limit)->toBe(250.0)
        ->and($updated->percentage_value)->toBe(25);
});

it('derives max_limit when only percentage_value is updated', function () {
    Account::factory()->create(['amount' => 1000, 'hidden' => false]);

    $budget = Budget::factory()->create(['max_limit' => 100, 'percentage_value' => 10]);

    $updated = app(BudgetRepository::class)->update(
        $budget,
        BudgetUpdateData::fromValidated(['percentage_value' => 50]),
    );

    expect((float) $updated->max_limit)->toBe(500.0)
        ->and($updated->percentage_value)->toBe(50);
});

it('calculates the daily limit from the budgets of the active period', function () {
    $period = Period::factory()->active()->create([
        'start_date' => '2026-01-01',
        'end_date' => '2026-01-11',
    ]);
    Budget::factory()->create(['period_id' => $period->id, 'max_limit' => 600]);
    Budget::factory()->create(['period_id' => $period->id, 'max_limit' => 400]);

    expect(app(BudgetRepository::class)->getDailyLimit())->toBe(100.0);
});

it('ignores budgets outside the active period when calculating the daily limit', function () {
    $activePeriod = Period::factory()->active()->create([
        'start_date' => '2026-01-01',
        'end_date' => '2026-01-11',
    ]);
    $inactivePeriod = Period::factory()->create();
    Budget::factory()->create(['period_id' => $activePeriod->id, 'max_limit' => 500]);
    Budget::factory()->create(['period_id' => $inactivePeriod->id, 'max_limit' => 9000]);
    Budget::factory()->create(['period_id' => null, 'max_limit' => 9000]);

    expect(app(BudgetRepository::class)->getDailyLimit())->toBe(50.0);
});

it('returns a daily limit of 0 when there is no active period', function () {
    $period = Period::factory()->create();
    Budget::factory()->create(['period_id' => $period->id]);

    expect(app(BudgetRepository::class)->getDailyLimit())->toEqual(0);
});

it('returns an expense percentage of 0 when there is no daily limit', function () {
    Expense::factory()->create(['created_at' => now()]);

    expect(app(ExpenseRepository::class)->getExpensePercentage())->toEqual(0);
});
