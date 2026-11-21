<?php

namespace App\Http\Controllers;

use App\DataTransferObjects\PeriodData;
use App\DataTransferObjects\PeriodUpdateData;
use App\Http\Requests\StorePeriodRequest;
use App\Http\Requests\UpdatePeriodRequest;
use App\Models\Period;
use App\Repositories\PeriodRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class PeriodController extends Controller
{
    public function __construct(private readonly PeriodRepository $periodRepository) {}

    public function index(): JsonResponse
    {
        return response()->json($this->periodRepository->get());
    }

    public function store(StorePeriodRequest $request): JsonResponse
    {
        $period = $this->periodRepository->store(PeriodData::fromValidated($request->validated()));

        return response()->json($period, 201);
    }

    public function update(UpdatePeriodRequest $request, Period $period): JsonResponse
    {
        $period = $this->periodRepository->update($period, PeriodUpdateData::fromValidated($request->validated()));

        return response()->json($period);
    }

    public function destroy(Period $period): Response
    {
        $this->periodRepository->delete($period);

        return response()->noContent();
    }
}
