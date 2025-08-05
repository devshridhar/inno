<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ArticleCollection;
use App\Http\Resources\ArticleResource;
use App\Models\Article;
use App\Models\UserArticleInteraction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\QueryBuilder;

class ArticleController extends Controller
{
    public function index(Request $request): ArticleCollection
    {
        $query = QueryBuilder::for(Article::class)
            ->allowedFilters([
                'title', 'author', 'news_source_id', 'category_id',
                'published_at', 'language', 'country'
            ])
            ->allowedSorts(['published_at', 'title', 'created_at'])
            ->with(['newsSource', 'category'])
            ->active()
            ->published();

        // Apply user preferences if authenticated
        if ($request->user()) {
            $preferences = $request->user()->userPreferences;
            if ($preferences) {
                $query = $this->applyUserPreferences($query, $preferences);
            }
        }

        $articles = $query->paginate($request->get('per_page', 20));

        return new ArticleCollection($articles);
    }

    public function show(Request $request, string $uuid): ArticleResource
    {
        $article = Article::where('uuid', $uuid)
            ->with(['newsSource', 'category'])
            ->active()
            ->firstOrFail();

        // Record view interaction if user is authenticated
        if ($request->user()) {
            UserArticleInteraction::updateOrCreate([
                'user_id' => $request->user()->id,
                'article_id' => $article->id,
                'interaction_type' => 'view'
            ], [
                'interacted_at' => now()
            ]);
        }

        return new ArticleResource($article);
    }

    public function bookmark(Request $request, string $uuid): JsonResponse
    {
        $article = Article::where('uuid', $uuid)->active()->firstOrFail();

        $interaction = UserArticleInteraction::updateOrCreate([
            'user_id' => $request->user()->id,
            'article_id' => $article->id,
            'interaction_type' => 'bookmark'
        ], [
            'interacted_at' => now()
        ]);

        return response()->json([
            'message' => 'Article bookmarked successfully',
            'bookmarked' => true
        ]);
    }

    public function removeBookmark(Request $request, string $uuid): JsonResponse
    {
        $article = Article::where('uuid', $uuid)->active()->firstOrFail();

        UserArticleInteraction::where([
            'user_id' => $request->user()->id,
            'article_id' => $article->id,
            'interaction_type' => 'bookmark'
        ])->delete();

        return response()->json([
            'message' => 'Bookmark removed successfully',
            'bookmarked' => false
        ]);
    }

    public function bookmarks(Request $request): ArticleCollection
    {
        $articles = QueryBuilder::for(Article::class)
            ->join('user_article_interactions', 'articles.id', '=', 'user_article_interactions.article_id')
            ->where('user_article_interactions.user_id', $request->user()->id)
            ->where('user_article_interactions.interaction_type', 'bookmark')
            ->with(['newsSource', 'category'])
            ->active()
            ->select('articles.*')
            ->orderBy('user_article_interactions.interacted_at', 'desc')
            ->paginate($request->get('per_page', 20));

        return new ArticleCollection($articles);
    }

    private function applyUserPreferences($query, $preferences)
    {
        // Filter by preferred sources
        if (!empty($preferences->preferred_sources)) {
            $query->whereIn('news_source_id', $preferences->preferred_sources);
        }

        // Filter by preferred categories
        if (!empty($preferences->preferred_categories)) {
            $query->whereIn('category_id', $preferences->preferred_categories);
        }

        // Block unwanted sources
        if (!empty($preferences->blocked_sources)) {
            $query->whereNotIn('news_source_id', $preferences->blocked_sources);
        }

        // Block articles with blocked keywords
        if (!empty($preferences->blocked_keywords)) {
            foreach ($preferences->blocked_keywords as $keyword) {
                $query->where(function ($q) use ($keyword) {
                    $q->where('title', 'not ilike', "%{$keyword}%")
                        ->where('description', 'not ilike', "%{$keyword}%");
                });
            }
        }

        return $query;
    }
}