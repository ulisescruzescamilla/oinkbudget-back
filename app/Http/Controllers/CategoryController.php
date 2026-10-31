<?php

namespace App\Http\Controllers;

use App\DataTransferObjects\CategoryData;
use App\DataTransferObjects\CategoryUpdateData;
use App\Http\Requests\StoreCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;
use App\Models\Category;
use App\Repositories\CategoryRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class CategoryController extends Controller
{
    public function __construct(private readonly CategoryRepository $categoryRepository) {}

    public function index(): JsonResponse
    {
        return response()->json($this->categoryRepository->get());
    }

    public function store(StoreCategoryRequest $request): JsonResponse
    {
        $category = $this->categoryRepository->store(CategoryData::fromValidated($request->validated()));

        return response()->json($category, 201);
    }

    public function update(UpdateCategoryRequest $request, Category $category): JsonResponse
    {
        $category = $this->categoryRepository->update($category, CategoryUpdateData::fromValidated($request->validated()));

        return response()->json($category);
    }

    public function destroy(Category $category): Response
    {
        $this->categoryRepository->delete($category);

        return response()->noContent();
    }
}
