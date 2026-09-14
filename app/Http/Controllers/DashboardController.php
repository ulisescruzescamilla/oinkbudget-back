<?php

namespace App\Http\Controllers;

use App\Repositories\BalanceRepository;
use App\Repositories\BudgetRepository;
use App\Repositories\ExpenseRepository;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    protected BudgetRepository $budgetRepository;

    protected ExpenseRepository $expenseRepository;

    protected BalanceRepository $balanceRepository;

    public function __construct(
        BudgetRepository $budgetRepository,
        ExpenseRepository $expenseRepository,
        BalanceRepository $balanceRepository
    ) {
        $this->budgetRepository = $budgetRepository;
        $this->expenseRepository = $expenseRepository;
        $this->balanceRepository = $balanceRepository;
    }

    public function index(Request $request)
    {
        $totalExpenseToday = $this->expenseRepository->getExpenseToday();

        $lastMoves = $this->balanceRepository->lastMoves();

        $dailyLimit = $this->budgetRepository->getDailyLimit();

        $dailyPct = $this->expenseRepository->getExpensePercentage();

        $last7Days = $this->expenseRepository->last7Days();

        return response()->json([
            'trend' => $last7Days,
            'percentage_expense_today' => $dailyPct,
            'total_expense_today' => $totalExpenseToday,
            'last_moves' => $lastMoves,
            'daily_limit' => $dailyLimit,
        ]);
    }
}
