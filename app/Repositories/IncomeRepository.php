<?php

namespace App\Repositories;

use App\DataTransferObjects\IncomeData;
use App\Models\Income;

class IncomeRepository
{
    public function store(IncomeData $data): Income
    {
        return Income::query()->create($data->toArray());
    }

    public function update(Income $income, IncomeData $data): Income
    {
        $income->update($data->toArray());

        return $income->fresh();
    }

    public function delete(Income $income): void
    {
        $income->delete();
    }
}
