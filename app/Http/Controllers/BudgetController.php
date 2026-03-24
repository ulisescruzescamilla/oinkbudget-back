<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBudgetRequest;
use App\Http\Requests\UpdateBudgetRequest;
use App\Models\Budget;
use App\Repositories\BudgetRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class BudgetController extends Controller
{
    public function __construct(private readonly BudgetRepository $budgetRepository) {}

    public function index(): JsonResponse
    {
        return response()->json(Budget::query()->get());
    }

    public function store(StoreBudgetRequest $request): JsonResponse
    {
        $budget = $this->budgetRepository->store($request->validated());

        return response()->json($budget, 201);
    }

    public function update(UpdateBudgetRequest $request, Budget $budget): JsonResponse
    {
        $budget = $this->budgetRepository->update($budget, $request->validated());

        return response()->json($budget);
    }

    public function destroy(Budget $budget): Response
    {
        $this->budgetRepository->delete($budget);

        return response()->noContent();
    }
}
