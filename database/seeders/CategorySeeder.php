<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Mercado', 'type' => 'expense', 'color' => '#8C6DE2', 'icon_code' => 'cart'],
            ['name' => 'Restaurantes', 'type' => 'expense', 'color' => '#D95544', 'icon_code' => 'fork'],
            ['name' => 'Transporte', 'type' => 'expense', 'color' => '#009EBE', 'icon_code' => 'car'],
            ['name' => 'Ocio', 'type' => 'expense', 'color' => '#BD5AB6', 'icon_code' => 'film'],
            ['name' => 'Salud', 'type' => 'expense', 'color' => '#D8516A', 'icon_code' => 'health'],
            ['name' => 'Servicios', 'type' => 'expense', 'color' => '#C56E00', 'icon_code' => 'bolt'],
            ['name' => 'Ahorro', 'type' => 'expense', 'color' => '#00A35C', 'icon_code' => 'piggy'],
            ['name' => 'Otro', 'type' => 'expense', 'color' => '#7575E9', 'icon_code' => 'star'],
        ];

        foreach ($categories as $category) {
            Category::query()->create($category);
        }
    }
}
