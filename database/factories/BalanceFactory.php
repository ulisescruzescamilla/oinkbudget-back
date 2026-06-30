<?php

namespace Database\Factories;

use App\Enums\BalanceTypeEnum;
use App\Models\Account;
use App\Models\Balance;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Balance>
 */
class BalanceFactory extends Factory
{
    public function definition(): array
    {
        return [
            'description' => fake()->sentence(),
            'amount' => fake()->randomFloat(3, 1, 10000),
            'type' => fake()->randomElement(BalanceTypeEnum::values()),
            'account_name' => fake()->word(),
            'account_id' => Account::factory(),
        ];
    }
}
