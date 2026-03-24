<?php

namespace Database\Factories;

use App\Models\Account;
use App\Models\Budget;
use App\Models\Expense;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Expense>
 */
class ExpenseFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'amount' => fake()->randomFloat(3, 1, 1000),
            'description' => fake()->sentence(),
            'budget_id' => Budget::factory(),
            'account_id' => Account::factory(),
        ];
    }
}
