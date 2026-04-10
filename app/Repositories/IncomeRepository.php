<?php

namespace App\Repositories;

use App\Models\Income;

class IncomeRepository
{
    public function store(array $data): Income
    {
        return Income::query()->create($data);
    }

    public function update(Income $income, array $data): Income
    {
        $income->update($data);

        return $income->fresh();
    }

    public function delete(Income $income): void
    {
        $income->delete();
    }
}
