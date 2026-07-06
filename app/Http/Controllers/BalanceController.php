<?php

namespace App\Http\Controllers;

use App\Http\Requests\IndexBalanceRequest;
use App\Repositories\BalanceRepository;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class BalanceController extends Controller
{
    public function __construct(private readonly BalanceRepository $balanceRepository) {}

    public function index(IndexBalanceRequest $request): JsonResponse
    {
        $order = $request->input('order', 'desc');

        
        if ($request->has('group_by')) {
            if ($request->input('group_by') === 'created_at') {
                // prepare dates
                $startDate = Carbon::parse($request->start_date ?? now())->startOfDay();
                $endDate = Carbon::parse($request->end_date ?? now())->endOfDay();

                // Log::debug("start: ". print_r($startDate->format('Y-m-d'), true));
                // Log::debug("end: ". print_r($endDate->format('Y-m-d'), true));

                return response()->json($this->balanceRepository->groupByDate($startDate, $endDate));
            }
        }

        if ($request->has('start_date') && $request->has('end_date')) {
            $response = $this->balanceRepository->filterByDate(
                $request->input('start_date'),
                $request->input('end_date'),
                $order,
            );
        } else {
            $response = $this->balanceRepository->get($order);
        }

        return response()->json($response);
    }
}
