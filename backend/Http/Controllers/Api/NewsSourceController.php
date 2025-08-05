<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\NewsSourceResource;
use App\Models\NewsSource;
use Illuminate\Http\JsonResponse;

class NewsSourceController extends Controller
{
    public function index(): JsonResponse
    {
        $sources = NewsSource::active()
            ->orderBy('name')
            ->withCount('articles')
            ->get();

        return response()->json([
            'sources' => NewsSourceResource::collection($sources)
        ]);
    }

    public function show(string $slug): JsonResponse
    {
        $source = NewsSource::where('slug', $slug)
            ->active()
            ->withCount('articles')
            ->firstOrFail();

        return response()->json([
            'source' => new NewsSourceResource($source)
        ]);
    }
}
