<?php

namespace App\Http\Controllers;

use App\DataTransferObjects\BudgetData;
use App\DataTransferObjects\BudgetUpdateData;
use App\Http\Requests\IndexBudgetRequest;
use App\Http\Requests\StoreBudgetRequest;
use App\Http\Requests\UpdateBudgetRequest;
use App\Models\Budget;
use App\Repositories\BudgetRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class BudgetController extends Controller
{
    public function __construct(private readonly BudgetRepository $budgetRepository) {}

    public function index(IndexBudgetRequest $request): JsonResponse
    {
        if ($request->filled('period_id')) {
            $response = $this->budgetRepository->filterByPeriod((int) $request->input('period_id'));
        } else {
            $response = $this->budgetRepository->get();
        }

        return response()->json($response);
    }

    public function store(StoreBudgetRequest $request): JsonResponse
    {
        $budget = $this->budgetRepository->store(BudgetData::fromValidated($request->validated()));

        return response()->json($budget, 201);
    }

    public function update(UpdateBudgetRequest $request, Budget $budget): JsonResponse
    {
        $budget = $this->budgetRepository->update($budget, BudgetUpdateData::fromValidated($request->validated()));

        return response()->json($budget);
    }

    public function destroy(Budget $budget): Response
    {
        $this->budgetRepository->delete($budget);

        return response()->noContent();
    }
}
