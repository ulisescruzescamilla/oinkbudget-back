<?php

namespace App\Repositories;

use App\Models\Budget;
use App\Models\Expense;
use Illuminate\Database\Eloquent\Collection;

class BudgetRepository
{
    protected AccountRepository $accountRepository;
    
    public function __construct(
        AccountRepository $accountRepository
    ) {
        $this->accountRepository = $accountRepository;
    }

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

    public function store(array $data): Budget
    {
        if (! isset($data['expense_amount'])) {
            $data['expense_amount'] = 0;
        }

        // calculate the percentage of this budget taking total available from balance
        $amountAvailable = $this->accountRepository->amountAvailable();
        
        $data['percentage_value'] = 0;

        if ($amountAvailable) {
            $data['percentage_value'] = round(($data['max_limit'] / $amountAvailable) * 100);
        }

        return Budget::query()->create($data);
    }

    public function update(Budget $budget, array $data): Budget
    {
        if (! isset($data['expense_amount'])) {
            $data['expense_amount'] = 0;
        }

        $amountAvailable = $this->accountRepository->amountAvailable();

        $data['percentage_value'] = 0;
        if ($amountAvailable) {
            $data['percentage_value'] = round(($data['max_limit'] / $amountAvailable) * 100);
        }

        $budget->update($data);

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
