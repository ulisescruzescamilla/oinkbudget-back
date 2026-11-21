<?php

namespace App\Repositories;

use App\DataTransferObjects\BudgetData;
use App\DataTransferObjects\BudgetUpdateData;
use App\Models\Budget;
use App\Models\Expense;
use App\Models\Period;
use App\Services\BudgetLimitCalculator;
use Illuminate\Database\Eloquent\Collection;

class BudgetRepository
{
    public function __construct(
        private readonly AccountRepository $accountRepository,
        private readonly BudgetLimitCalculator $limitCalculator,
    ) {}

    public function get(): Collection
    {
        // TODO sort
        return Budget::query()->with('category')->get();
    }

    public function filterByPeriod(int $periodId): Collection
    {
        // TODO sort
        return Budget::query()->with('category')->where('period_id', $periodId)->get();
    }

    public function store(BudgetData $data): Budget
    {
        if ($data->client_id) {
            $existing = Budget::query()->where('client_id', $data->client_id)->first();

            if ($existing) {
                return $existing;
            }
        }

        $limits = $this->limitCalculator->calculate(
            $data->max_limit,
            $data->percentage_value,
            $this->accountRepository->amountAvailable(),
        );

        return Budget::query()->create([...$data->toArray(), ...$limits]);
    }

    public function update(Budget $budget, BudgetUpdateData $data): Budget
    {
        $changes = $data->toArray();

        if ($data->max_limit !== null || $data->percentage_value !== null) {
            $changes = [
                ...$changes,
                ...$this->limitCalculator->calculate(
                    $data->max_limit,
                    $data->percentage_value,
                    $this->accountRepository->amountAvailable(),
                ),
            ];
        }

        $budget->update($changes);

        return $budget->fresh();
    }

    public function updateBudgetExpenses(Expense $expense)
    {
        $budget = $expense->budget;

        $budget->expense_amount = round($expense->amount + $budget->expense_amount, 2);
        $budget->save();
    }

    public function delete(Budget $budget): void
    {
        $budget->delete();
    }

    /**
     * Average daily limit of the budgets that belong to the active period.
     */
    public function getDailyLimit(): float
    {
        $period = Period::query()->where('is_active', true)->first();

        if (! $period) {
            return 0;
        }

        $daysInPeriod = $period->start_date->diffInDays($period->end_date);

        if (! $daysInPeriod) {
            return 0;
        }

        return round($period->budgets()->sum('max_limit') / $daysInPeriod, 2);
    }
}
