<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreIncomeRequest;
use App\Http\Requests\UpdateIncomeRequest;
use App\Models\Income;
use App\Repositories\IncomeRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class IncomeController extends Controller
{
    public function __construct(private readonly IncomeRepository $incomeRepository) {}

    public function index(): JsonResponse
    {
        return response()->json(Income::query()->get());
    }

    public function store(StoreIncomeRequest $request): JsonResponse
    {
        $income = $this->incomeRepository->store($request->validated());

        return response()->json($income, 201);
    }

    public function update(UpdateIncomeRequest $request, Income $income): JsonResponse
    {
        $income = $this->incomeRepository->update($income, $request->validated());

        return response()->json($income);
    }

    public function destroy(Income $income): Response
    {
        $this->incomeRepository->delete($income);

        return response()->noContent();
    }
}
