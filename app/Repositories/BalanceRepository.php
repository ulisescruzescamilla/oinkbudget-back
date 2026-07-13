<?php

namespace App\Repositories;

use App\DataTransferObjects\BalanceData;
use App\Models\Balance;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log;

class BalanceRepository
{
    public function get(string $order = 'desc'): Collection
    {
        return Balance::query()
            ->with('account')
            //->whereDate('created_at', today())
            ->orderBy('created_at', $order)
            ->get();
    }

    public function groupByDate(Carbon $startDate, Carbon $endDate): \Illuminate\Support\Collection
    {
        // today query by default
        if (!$startDate || !$endDate) {
            $startDate = Carbon::now()->startOfDay();
            $endDate = Carbon::now()->endOfDay();
        }

        $collection =  Balance::query()
            ->with('account')
            ->whereBetween('created_at', [$startDate->format('Y-m-d H:i:s'), $endDate->format('Y-m-d H:i:s')])
            ->orderBy('created_at', 'desc')
            ->get();

        return collect($collection)->groupBy(function ($balance) {
            return substr($balance['created_at'], 0, 10);
        });

    }

    public function filterByDate(string $startDate, string $endDate, string $order = 'desc'): Collection
    {
        return Balance::query()
            ->with('account')
            ->whereDate('created_at', '>=', $startDate)
            ->whereDate('created_at', '<=', $endDate)
            ->orderBy('created_at', $order)
            ->get();
    }

    public function store(BalanceData $data): Balance
    {
        return Balance::query()->create($data->toArray());
    }

    public function update(Balance $balance, BalanceData $data): Balance
    {
        $balance->update($data->toArray());

        return $balance->fresh();
    }

    public function delete(Balance $balance): void
    {
        $balance->delete();
    }
}
