<?php

namespace Database\Factories;

use App\Models\Period;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

/**
 * @extends Factory<Period>
 */
class PeriodFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startDate = Carbon::instance(fake()->dateTimeBetween('-1 year', 'now'))->startOfMonth();

        return [
            'start_date' => $startDate->format('Y-m-d'),
            'end_date' => $startDate->copy()->endOfMonth()->format('Y-m-d'),
            'is_active' => false,
        ];
    }

    /**
     * Indicate that the period is the active one.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes): array => [
            'is_active' => true,
        ]);
    }
}
