<?php

namespace App\Repositories;

use App\DataTransferObjects\CategoryData;
use App\DataTransferObjects\CategoryUpdateData;
use App\Models\Category;
use Illuminate\Database\Eloquent\Collection;

class CategoryRepository
{
    public function get(): Collection
    {
        return Category::query()->get();
    }

    public function store(CategoryData $data): Category
    {
        return Category::query()->create($data->toArray());
    }

    public function update(Category $category, CategoryUpdateData $data): Category
    {
        $category->update($data->toArray());

        return $category->fresh();
    }

    /**
     * Soft delete the category when budgets still reference it so they keep their
     * category, otherwise remove it permanently.
     */
    public function delete(Category $category): void
    {
        if ($category->budgets()->exists()) {
            $category->delete();

            return;
        }

        $category->forceDelete();
    }
}
