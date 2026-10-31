<?php

namespace App\Http\Controllers;

use App\DataTransferObjects\RecurrentBudgetData;
use App\DataTransferObjects\RecurrentBudgetUpdateData;
use App\Http\Requests\StoreRecurrentBudgetRequest;
use App\Http\Requests\UpdateRecurrentBudgetRequest;
use App\Models\RecurrentBudget;
use App\Repositories\RecurrentBudgetRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class RecurrentBudgetController extends Controller
{
    public function __construct(private readonly RecurrentBudgetRepository $recurrentBudgetRepository) {}

    public function index(): JsonResponse
    {
        return response()->json($this->recurrentBudgetRepository->get());
    }

    public function store(StoreRecurrentBudgetRequest $request): JsonResponse
    {
        $recurrentBudget = $this->recurrentBudgetRepository->store(RecurrentBudgetData::fromValidated($request->validated()));

        return response()->json($recurrentBudget, 201);
    }

    public function update(UpdateRecurrentBudgetRequest $request, RecurrentBudget $recurrentBudget): JsonResponse
    {
        $recurrentBudget = $this->recurrentBudgetRepository->update($recurrentBudget, RecurrentBudgetUpdateData::fromValidated($request->validated()));

        return response()->json($recurrentBudget);
    }

    public function destroy(RecurrentBudget $recurrentBudget): Response
    {
        $this->recurrentBudgetRepository->delete($recurrentBudget);

        return response()->noContent();
    }
}
