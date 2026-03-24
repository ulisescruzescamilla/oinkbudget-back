<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreExpenseRequest;
use App\Http\Requests\UpdateExpenseRequest;
use App\Models\Expense;
use App\Repositories\ExpenseRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class ExpenseController extends Controller
{
    public function __construct(private readonly ExpenseRepository $expenseRepository) {}

    public function index(): JsonResponse
    {
        return response()->json(Expense::query()->get());
    }

    public function store(StoreExpenseRequest $request): JsonResponse
    {
        $expense = $this->expenseRepository->store($request->validated());

        return response()->json($expense, 201);
    }

    public function update(UpdateExpenseRequest $request, Expense $expense): JsonResponse
    {
        $expense = $this->expenseRepository->update($expense, $request->validated());

        return response()->json($expense);
    }

    public function destroy(Expense $expense): Response
    {
        $this->expenseRepository->delete($expense);

        return response()->noContent();
    }
}
