<?php

namespace App\Repositories;

use App\Models\Budget;
use App\Models\Income;
use Illuminate\Database\Eloquent\Collection;

class BudgetRepository
{
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

        // calculate total of incomes and take amount from max_limit to calculate percentage value
        if (! isset($data['percentage_value']) && isset($data['max_limit'])) {
            // TODO use repository filtered by date range
            $totalIncomes = Income::query()->sum('amount');
            $data['percentage_value'] = $totalIncomes > 0 ? (int) round(($data['max_limit'] / $totalIncomes) * 100) : 0;
        }

        if (! isset($data['max_limit']) && isset($data['percentage_value'])) {
            // TODO use repository filtered by date range
            $totalIncomes = Income::query()->sum('amount');
            $data['max_limit'] = ($totalIncomes * $data['percentage_value']) / 100;
        }

        return Budget::query()->create($data);
    }

    public function update(Budget $budget, array $data): Budget
    {
        if (! isset($data['expense_amount'])) {
            $data['expense_amount'] = 0;
        }

        // calculate total of incomes and take amount from max_limit to calculate percentage value
        if (! isset($data['percentage_value']) && isset($data['max_limit'])) {
            // TODO use repository filtered by date range
            $totalIncomes = Income::query()->sum('amount');
            $data['percentage_value'] = $totalIncomes > 0 ? (int) round(($data['max_limit'] / $totalIncomes) * 100) : 0;
        }

        if (! isset($data['max_limit']) && isset($data['percentage_value'])) {
            // TODO use repository filtered by date range
            $totalIncomes = Income::query()->sum('amount');
            $data['max_limit'] = ($totalIncomes * $data['percentage_value']) / 100;
        }

        $budget->update($data);

        return $budget->fresh();
    }

    public function delete(Budget $budget): void
    {
        $budget->delete();
    }
}
