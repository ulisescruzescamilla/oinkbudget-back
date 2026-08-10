<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Mercado', 'color' => '#8C6DE2', 'icon_code' => 'cart'],
            ['name' => 'Restaurantes', 'color' => '#D95544', 'icon_code' => 'fork'],
            ['name' => 'Transporte', 'color' => '#009EBE', 'icon_code' => 'car'],
            ['name' => 'Ocio', 'color' => '#BD5AB6', 'icon_code' => 'film'],
            ['name' => 'Salud', 'color' => '#D8516A', 'icon_code' => 'health'],
            ['name' => 'Servicios', 'color' => '#C56E00', 'icon_code' => 'bolt'],
            ['name' => 'Ahorro', 'color' => '#00A35C', 'icon_code' => 'piggy'],
            ['name' => 'Otro', 'color' => '#7575E9', 'icon_code' => 'star'],
        ];

        foreach ($categories as $category) {
            Category::query()->create($category);
        }
    }
}
