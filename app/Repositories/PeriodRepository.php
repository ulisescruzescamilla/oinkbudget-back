<?php

namespace App\Repositories;

use App\DataTransferObjects\PeriodData;
use App\DataTransferObjects\PeriodUpdateData;
use App\Models\Period;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class PeriodRepository
{
    public function get(): Collection
    {
        return Period::query()->orderByDesc('start_date')->get();
    }

    /**
     * Only one period can be active, so activating this one deactivates the rest.
     */
    public function store(PeriodData $data): Period
    {
        return DB::transaction(function () use ($data): Period {
            if ($data->is_active) {
                $this->deactivateOthers();
            }

            return Period::query()->create($data->toArray());
        });
    }

    /**
     * Only one period can be active, so activating this one deactivates the rest.
     */
    public function update(Period $period, PeriodUpdateData $data): Period
    {
        return DB::transaction(function () use ($period, $data): Period {
            if ($data->is_active === true) {
                $this->deactivateOthers($period);
            }

            $period->update($data->toArray());

            return $period->fresh();
        });
    }

    /**
     * Budgets linked to the period keep existing; their period_id is nulled by the foreign key.
     */
    public function delete(Period $period): void
    {
        $period->delete();
    }

    private function deactivateOthers(?Period $except = null): void
    {
        Period::query()
            ->where('is_active', true)
            ->when($except, fn ($query) => $query->whereKeyNot($except->getKey()))
            ->update(['is_active' => false]);
    }
}
