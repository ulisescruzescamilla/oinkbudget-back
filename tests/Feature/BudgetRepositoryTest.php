<?php

use App\DataTransferObjects\BudgetUpdateData;
use App\Models\Account;
use App\Models\Budget;
use App\Repositories\BudgetRepository;
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
