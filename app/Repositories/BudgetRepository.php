<?php

namespace App\Repositories;

use App\Models\Budget;

class BudgetRepository
{
    public function store(array $data): Budget
    {
        return Budget::query()->create($data);
    }

    public function update(Budget $budget, array $data): Budget
    {
        $budget->update($data);

        return $budget->fresh();
    }

    public function delete(Budget $budget): void
    {
        $budget->delete();
    }
}
