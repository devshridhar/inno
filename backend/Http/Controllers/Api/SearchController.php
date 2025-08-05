<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Article\SearchArticleRequest;
use App\Http\Resources\ArticleCollection;
use App\Models\Article;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\QueryBuilder;

class SearchController extends Controller
{
    public function search(SearchArticleRequest $request): ArticleCollection
    {
        $query = QueryBuilder::for(Article::class)
            ->with(['newsSource', 'category'])
            ->active()
            ->published();

        // Text search
        if ($request->filled('q')) {
            $searchTerm = $request->q;
            $query->where(function ($q) use ($searchTerm) {
                $q->whereFullText(['title', 'description', 'content'], $searchTerm)
                    ->orWhere('title', 'ilike', "%{$searchTerm}%")
                    ->orWhere('description', 'ilike', "%{$searchTerm}%")
                    ->orWhere('author', 'ilike', "%{$searchTerm}%");
            });
        }

        // Date filters
        if ($request->filled('from_date')) {
            $query->where('published_at', '>=', $request->from_date);
        }

        if ($request->filled('to_date')) {
            $query->where('published_at', '<=', $request->to_date);
        }

        // Category filter
        if ($request->filled('category')) {
            if (is_array($request->category)) {
                $query->whereIn('category_id', $request->category);
            } else {
                $query->where('category_id', $request->category);
            }
        }

        // Source filter
        if ($request->filled('source')) {
            if (is_array($request->source)) {
                $query->whereIn('news_source_id', $request->source);
            } else {
                $query->where('news_source_id', $request->source);
            }
        }

        // Author filter
        if ($request->filled('author')) {
            $query->where('author', 'ilike', "%{$request->author}%");
        }

        // Language filter
        if ($request->filled('language')) {
            $query->where('language', $request->language);
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'published_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $articles = $query->paginate($request->get('per_page', 20));

        return new ArticleCollection($articles);
    }

    public function suggestions(Request $request): JsonResponse
    {
        $query = $request->get('q', '');

        if (strlen($query) < 2) {
            return response()->json(['suggestions' => []]);
        }

        // Get title suggestions
        $titleSuggestions = Article::select('title')
            ->where('title', 'ilike', "%{$query}%")
            ->active()
            ->published()
            ->limit(5)
            ->pluck('title')
            ->toArray();

        // Get author suggestions
        $authorSuggestions = Article::select('author')
            ->where('author', 'ilike', "%{$query}%")
            ->whereNotNull('author')
            ->active()
            ->published()
            ->distinct()
            ->limit(3)
            ->pluck('author')
            ->toArray();

        return response()->json([
            'suggestions' => [
                'titles' => $titleSuggestions,
                'authors' => $authorSuggestions,
            ]
        ]);
    }
}