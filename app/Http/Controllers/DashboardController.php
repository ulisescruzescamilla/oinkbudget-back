<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Repositories\BudgetRepository;
use App\Repositories\ExpenseRepository;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class DashboardController extends Controller
{
    protected BudgetRepository $budgetRepository;
    protected ExpenseRepository $expenseRepository;

    public function __construct(
        BudgetRepository $budgetRepository,
        ExpenseRepository $expenseRepository
    ) {
        $this->budgetRepository = $budgetRepository;
        $this->expenseRepository = $expenseRepository;
    }

    public function index(Request $request)
    {
        $totalExpenseToday = $this->expenseRepository->getExpenseToday();

        $lastMoves = $this->expenseRepository->lastMoves();

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
