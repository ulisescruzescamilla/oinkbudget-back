<?php

namespace Database\Factories;

use App\Models\Budget;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Budget>
 */
class BudgetFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'max_limit' => fake()->randomFloat(2, 100, 5000),
            'expense_amount' => fake()->randomFloat(2, 0, 1000),
            'percentage_value' => fake()->numberBetween(1, 100),
            'graph_color' => fake()->regexify('[A-F0-9]{6}'),
            'start_date' => $startDate = fake()->dateTimeBetween('-1 year', 'now')->format('Y-m-d'),
            'end_date' => fake()->dateTimeBetween($startDate, '+1 year')->format('Y-m-d'),
        ];
    }
}
