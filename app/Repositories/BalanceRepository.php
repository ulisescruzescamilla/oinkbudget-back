<?php

namespace App\Repositories;

use App\DataTransferObjects\BalanceData;
use App\Models\Balance;
use Illuminate\Database\Eloquent\Collection;

class BalanceRepository
{
    public function get(string $order = 'desc'): Collection
    {
        return Balance::query()
            ->with('account')
            ->whereDate('created_at', today())
            ->orderBy('created_at', $order)
            ->get();
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
