<?php

namespace App\Repositories;

use App\DataTransferObjects\BudgetData;
use App\DataTransferObjects\BudgetUpdateData;
use App\Models\Budget;
use App\Models\Expense;
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
        return Budget::query()->get();
    }

    public function filterByDate(string $startDate, string $endDate): Collection
    {
        $query = Budget::query();

        $query->where('start_date', $startDate)
            ->where('end_date', $endDate);

        // TODO sort

        return $query->get();
    }

    public function store(BudgetData $data): Budget
    {
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

        $budget->expense_amount = round($expense->amount + $budget->expense_amount , 2);
        $budget->save();
    }

    public function delete(Budget $budget): void
    {
        $budget->delete();
    }
    
    /**
     * Take current budgets and calculate an average of daily limit
     *
     * @return float
     */
    public function getDailyLimit(): float
    {
        $start = Budget::query()->where('is_active', true)->orderBy('start_date', 'asc');
        $end = Budget::query()->where('is_active', true)->orderBy('end_date', 'desc');

        $budgets = $start->get();

        if ($budgets->isEmpty()) {
            return 0;
        }

        $startDate = $start->first()->start_date;
        $endDate = $end->first()->end_date;

        $daysInPeriod = $startDate->diffInDays($endDate);

        return round($budgets->sum('max_limit') / $daysInPeriod, 2);
    }
}
