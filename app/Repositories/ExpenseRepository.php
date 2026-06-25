<?php

namespace App\Repositories;

use App\Models\Expense;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Collection;

class ExpenseRepository
{
    protected BudgetRepository $budgetRepository;

    public function __construct(BudgetRepository $budgetRepository)
    {
        $this->budgetRepository = $budgetRepository;
    }

    public function store(array $data): Expense
    {
        // save expense
        $expense =  Expense::query()->create($data);
        // update budget expenses amount
        $this->budgetRepository->updateBudgetExpenses($expense);

        return $expense;
    }

    /**
     * Calculate expense from the last 7 days
     * @return array<int, float>
     */
    public function last7Days(): array
    {
        // calc period of dates
        $startDate = now()->subDays(6)->startOfDay();
        $endDate = now()->endOfDay();
        $period = CarbonPeriod::create($startDate, $endDate);

        // create an array of total expenses by date ['2026-01-01' => 120, ...]
        $totalsByDate = Expense::query()
            ->selectRaw('DATE(created_at) as date, SUM(amount) as total')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('date')
            ->pluck('total', 'date');

        // frontend needs specific format [{d,v},...] for graphs
        return collect($period)
            ->map(function(Carbon $date) use ($totalsByDate) {
                return [
                    "d" => strtoupper(substr($date->format('D'), 0, 1)),
                    "v" => (float) ($totalsByDate[$date->toDateString()] ?? 0)
                ];
            })
            ->all();
    }

    public function update(Expense $expense, array $data): Expense
    {
        $budget = $expense->budget;

        // remove old amount form this sum
        $budget->expense_amount = round($budget->expense_amount - $expense->amount, 2);
        $budget->save();
        
        // update 
        $expense->update($data);
        $expense = $expense->fresh(); // refresh collection
        $this->budgetRepository->updateBudgetExpenses($expense);

        return $expense;
    }

    public function delete(Expense $expense): void
    {
        $expense->delete();
    }

    public function getExpenseToday(): float
    {
        return Expense::whereBetween('created_at', [now()->startOfDay(), now()->endOfDay()])
            ->sum('amount');
    }

    public function getExpensePercentage(): float
    {
        $totalExpenseToday = $this->getExpenseToday();

        $dailyLimit = $this->budgetRepository->getDailyLimit();

        if (!$totalExpenseToday) {
            return 0;
        }

        $dailyPct = round(($totalExpenseToday * 100) / $dailyLimit, 2) ?? 0;

        return $dailyPct;
    }

    public function lastMoves() : Collection
    {
        return Expense::whereBetween('created_at', [now()->startOfDay(), now()->endOfDay()])
            ->latest()
            ->take(10)
            ->get();
    }
}
