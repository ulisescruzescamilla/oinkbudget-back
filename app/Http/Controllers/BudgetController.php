<?php

namespace App\Http\Controllers;

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
        if ($request->has('start_date') && $request->has('end_date')) {
            $response = $this->budgetRepository->filterByDate(
                $request->input('start_date'),
                $request->input('end_date')
            );

        } else {
            $response = $this->budgetRepository->get();
        }

        return response()->json($response);
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
