<?php

namespace App\Repositories;

use App\DataTransferObjects\BalanceData;
use App\Models\Balance;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

class BalanceRepository
{
    public function filterByRangeAndType(?string $range, ?string $type, string $order = 'desc'): Collection
    {
        $query = Balance::query()->with('account');

        match ($range ?? 'today') {
            'today' => $query->whereDate('created_at', today()),
            'week' => $query->whereDate('created_at', '>=', today()->subDays(6)),
            'month' => $query->whereDate('created_at', '>=', today()->subDays(29)),
            default => null,
        };

        if ($type && $type !== 'all') {
            $query->where('type', $type);
        }

        return $query->orderBy('created_at', $order)->get();
    }

    public function groupByDate(Carbon $startDate, Carbon $endDate): \Illuminate\Support\Collection
    {
        // today query by default
        if (! $startDate || ! $endDate) {
            $startDate = Carbon::now()->startOfDay();
            $endDate = Carbon::now()->endOfDay();
        }

        $collection = Balance::query()
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

    public function lastMoves(int $limit = 10): Collection
    {
        return Balance::query()
            ->with('account')
            ->whereBetween('created_at', [now()->startOfDay(), now()->endOfDay()])
            ->orderBy('created_at', 'desc')
            ->orderBy('id', 'desc')
            ->take($limit)
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
