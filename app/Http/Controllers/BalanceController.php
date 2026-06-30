<?php

namespace App\Http\Controllers;

use App\Http\Requests\IndexBalanceRequest;
use App\Repositories\BalanceRepository;
use Illuminate\Http\JsonResponse;

class BalanceController extends Controller
{
    public function __construct(private readonly BalanceRepository $balanceRepository) {}

    public function index(IndexBalanceRequest $request): JsonResponse
    {
        $order = $request->input('order', 'desc');

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
