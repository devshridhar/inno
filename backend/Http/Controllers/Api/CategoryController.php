<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use Illuminate\Http\JsonResponse;

class CategoryController extends Controller
{
    public function index(): JsonResponse
    {
        $categories = Category::active()
            ->ordered()
            ->withCount('articles')
            ->get();

        return response()->json([
            'categories' => CategoryResource::collection($categories)
        ]);
    }

    public function show(string $slug): JsonResponse
    {
        $category = Category::where('slug', $slug)
            ->active()
            ->withCount('articles')
            ->firstOrFail();

        return response()->json([
            'category' => new CategoryResource($category)
        ]);
    }
}