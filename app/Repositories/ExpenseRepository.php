<?php

namespace App\Repositories;

use App\Models\Expense;

class ExpenseRepository
{
    public function store(array $data): Expense
    {
        return Expense::query()->create($data);
    }

    public function update(Expense $expense, array $data): Expense
    {
        $expense->update($data);

        return $expense->fresh();
    }

    public function delete(Expense $expense): void
    {
        $expense->delete();
    }
}
