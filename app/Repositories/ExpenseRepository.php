<?php

namespace App\Repositories;

use App\DataTransferObjects\BalanceData;
use App\DataTransferObjects\ExpenseData;
use App\Enums\BalanceTypeEnum;
use App\Models\Expense;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Collection;

class ExpenseRepository
{
    public function __construct(
        private readonly BudgetRepository $budgetRepository,
        private readonly BalanceRepository $balanceRepository,
    ) {}

    public function store(ExpenseData $data): Expense
    {
        // save expense
        $expense = Expense::query()->create($data->toArray());
        // update budget expenses amount
        $this->budgetRepository->updateBudgetExpenses($expense);

        // create balance record
        $account = $expense->account;

        $balanceData = new BalanceData(
            description: $data->description,
            amount: $data->amount,
            type: BalanceTypeEnum::EXPENSE,
            account_name: $account->name,
            account_id: $data->account_id,
            balanceable_type: $expense::class,
            balanceable_id: $expense->id,
        );

        $this->balanceRepository->store($balanceData);

        return $expense->fresh('balance');
    }

    /**
     * Calculate expense from the last 7 days
     *
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
            ->map(function (Carbon $date) use ($totalsByDate) {
                return [
                    'd' => strtoupper(substr($date->format('D'), 0, 1)),
                    'v' => (float) ($totalsByDate[$date->toDateString()] ?? 0),
                ];
            })
            ->all();
    }

    public function update(Expense $expense, ExpenseData $data): Expense
    {
        $budget = $expense->budget;

        // remove old amount form this sum
        $budget->expense_amount = round($budget->expense_amount - $expense->amount, 2);
        $budget->save();

        // update
        $expense->update($data->toArray());
        $expense = $expense->fresh(); // refresh collection
        $this->budgetRepository->updateBudgetExpenses($expense);

        // update balance record
        $account = $expense->account;

        $balanceData = new BalanceData(
            description: $data->description,
            amount: $data->amount,
            type: BalanceTypeEnum::EXPENSE,
            account_name: $account->name,
            account_id: $data->account_id,
            balanceable_type: $expense::class,
            balanceable_id: $expense->id,
        );

        if ($expense->balance) {
            $this->balanceRepository->update($expense->balance, $balanceData);
        } else {
            $balance = $this->balanceRepository->store($balanceData);
            $expense->balance()->save($balance);
        }

        return $expense->fresh('balance');
    }

    public function delete(Expense $expense): void
    {
        $expense->balance()?->delete();
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

        if (! $totalExpenseToday) {
            return 0;
        }

        $dailyPct = round(($totalExpenseToday * 100) / $dailyLimit, 2) ?? 0;

        return $dailyPct;
    }

    public function lastMoves(): Collection
    {
        return Expense::whereBetween('created_at', [now()->startOfDay(), now()->endOfDay()])
            ->latest()
            ->take(10)
            ->get();
    }
}
