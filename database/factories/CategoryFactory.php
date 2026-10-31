<?php

namespace Database\Factories;

use App\Enums\BalanceTypeEnum;
use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Category> */
class CategoryFactory extends Factory
{
    protected $model = Category::class;

    public function definition(): array
    {
        return [
            'name' => fake()->word(),
            'type' => fake()->randomElement(BalanceTypeEnum::values()),
            'color' => fake()->hexColor(),
            'icon_code' => fake()->lexify('??????????'),
        ];
    }
}
