<?php

namespace App\Services;

final class BudgetLimitCalculator
{
    /**
     * Derive the half of the max_limit/percentage_value pair that wasn't supplied,
     * relative to the total amount available across non-hidden accounts.
     *
     * @return array{max_limit: float, percentage_value: int}
     */
    public function calculate(?float $maxLimit, ?int $percentageValue, float $amountAvailable): array
    {
        if ($maxLimit !== null) {
            return [
                'max_limit' => $maxLimit,
                'percentage_value' => $amountAvailable > 0 ? (int) round(($maxLimit / $amountAvailable) * 100) : 0,
            ];
        }

        if ($percentageValue !== null) {
            return [
                'max_limit' => $amountAvailable > 0 ? round(($percentageValue / 100) * $amountAvailable, 2) : 0.0,
                'percentage_value' => $percentageValue,
            ];
        }

        return ['max_limit' => 0.0, 'percentage_value' => 0];
    }
}
