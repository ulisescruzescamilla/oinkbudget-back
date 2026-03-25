<?php

namespace Database\Factories;

use App\Models\Account;
use Illuminate\Database\Eloquent\Factories\Factory;

class IncomeFactory extends Factory
{
    public function definition(): array
    {
        return [
            'amount' => fake()->randomFloat(3, 1, 10000),
            'description' => fake()->sentence(),
            'account_id' => Account::factory(),
        ];
    }
}
