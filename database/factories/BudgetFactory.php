<?php

namespace Database\Factories;

use App\Models\Budget;
use App\Models\Category;
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
            'name' => fake()->sentence(),
            'max_limit' => fake()->randomFloat(2, 100, 5000),
            'expense_amount' => fake()->randomFloat(2, 0, 1000),
            'percentage_value' => fake()->numberBetween(1, 100),
            'category_id' => Category::factory(),
        ];
    }
}
