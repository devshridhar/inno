<?php
// ./backend/app/Services/NewsScrapers/NewsAPIService.php

namespace App\Services\NewsScrapers;

use App\Models\Article;
use App\Models\Category;
use App\Models\NewsSource;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Carbon\Carbon;

class NewsAPIService
{
    private string $apiKey;
    private string $baseUrl;

    public function __construct()
    {
        $this->apiKey = config('news.apis.newsapi.key');
        $this->baseUrl = config('news.apis.newsapi.url');
    }

    public function scrapeArticles(NewsSource $source): array
    {
        try {
            $response = Http::timeout(30)
                ->get($this->baseUrl . '/top-headlines', [
                    'apiKey' => $this->apiKey,
                    'country' => 'us', // Get US top headlines
                    'pageSize' => config('news.scraping.max_articles_per_source', 100),
                ]);

            if (!$response->successful()) {
                throw new \Exception('API request failed: ' . $response->body());
            }

            $data = $response->json();
            $articles = $data['articles'] ?? [];

            $savedCount = 0;
            $skippedCount = 0;

            foreach ($articles as $article) {
                if ($this->saveArticle($article, $source)) {
                    $savedCount++;
                } else {
                    $skippedCount++;
                }
            }

            return [
                'total_fetched' => count($articles),
                'saved' => $savedCount,
                'skipped' => $skippedCount,
                'source' => $source->name
            ];

        } catch (\Exception $e) {
            Log::error('NewsAPI scraping failed', [
                'source' => $source->name,
                'error' => $e->getMessage()
            ]);

            return [
                'error' => $e->getMessage(),
                'source' => $source->name
            ];
        }
    }

    private function saveArticle(array $data, NewsSource $source): bool
    {
        // Skip if article already exists
        if (Article::where('url', $data['url'])->exists()) {
            return false;
        }

        // Skip if required fields are missing
        if (empty($data['title']) || empty($data['url'])) {
            return false;
        }

        // Determine category
        $category = $this->determineCategory($data);

        Article::create([
            'title' => $data['title'],
            'description' => $data['description'],
            'content' => $data['content'] ?? $data['description'],
            'url' => $data['url'],
            'image_url' => $data['urlToImage'],
            'author' => $data['author'],
            'news_source_id' => $source->id,
            'category_id' => $category?->id,
            'published_at' => Carbon::parse($data['publishedAt']),
            'metadata' => [
                'source_api' => 'newsapi',
                'original_data' => $data
            ]
        ]);

        return true;
    }

    private function determineCategory(array $data): ?Category
    {
        $title = strtolower($data['title'] ?? '');
        $description = strtolower($data['description'] ?? '');
        $content = $title . ' ' . $description;

        // Simple keyword-based categorization
        $categoryKeywords = [
            'technology' => ['tech', 'ai', 'software', 'app', 'digital', 'computer', 'internet'],
            'business' => ['business', 'economy', 'finance', 'market', 'stock', 'company'],
            'sports' => ['sport', 'game', 'team', 'player', 'match', 'football', 'basketball'],
            'health' => ['health', 'medical', 'doctor', 'hospital', 'disease', 'vaccine'],
            'science' => ['science', 'research', 'study', 'discovery', 'scientist'],
            'entertainment' => ['movie', 'music', 'celebrity', 'film', 'actor', 'entertainment'],
        ];

        foreach ($categoryKeywords as $categorySlug => $keywords) {
            foreach ($keywords as $keyword) {
                if (str_contains($content, $keyword)) {
                    return Category::where('slug', $categorySlug)->first();
                }
            }
        }

        return Category::where('slug', 'general')->first();
    }
}
