<?php

namespace Database\Factories;

use App\Models\RecurrentBudget;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<RecurrentBudget> */
class RecurrentBudgetFactory extends Factory
{
    protected $model = RecurrentBudget::class;

    public function definition(): array
    {
        return [
            'name' => fake()->word(),
            'color' => fake()->hexColor(),
            'icon_code' => fake()->lexify('??????????'),
        ];
    }
}
