<?php

namespace App\Repositories;

use App\DataTransferObjects\RecurrentBudgetData;
use App\DataTransferObjects\RecurrentBudgetUpdateData;
use App\Models\RecurrentBudget;
use Illuminate\Database\Eloquent\Collection;

class RecurrentBudgetRepository
{
    public function get(): Collection
    {
        return RecurrentBudget::query()->get();
    }

    public function store(RecurrentBudgetData $data): RecurrentBudget
    {
        return RecurrentBudget::query()->create($data->toArray());
    }

    public function update(RecurrentBudget $recurrentBudget, RecurrentBudgetUpdateData $data): RecurrentBudget
    {
        $recurrentBudget->update($data->toArray());

        return $recurrentBudget->fresh();
    }

    /**
     * Always soft delete the recurrent budget, whether or not anything uses it.
     */
    public function delete(RecurrentBudget $recurrentBudget): void
    {
        $recurrentBudget->delete();
    }
}
