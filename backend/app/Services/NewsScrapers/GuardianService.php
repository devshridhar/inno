<?php

namespace App\Services\NewsScrapers;

use App\Models\Article;
use App\Models\Category;
use App\Models\NewsSource;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class GuardianService
{
    private string $apiKey;
    private string $baseUrl;

    public function __construct()
    {
        $this->apiKey = config('news.apis.guardian.key');
        $this->baseUrl = config('news.apis.guardian.url');
    }

    public function scrapeArticles(NewsSource $source): array
    {
        try {
            $response = Http::timeout(30)
                ->get($this->baseUrl . '/search', [
                    'api-key' => $this->apiKey,
                    'page-size' => config('news.scraping.max_articles_per_source', 100),
                    'show-fields' => 'headline,trailText,body,thumbnail,byline',
                    'order-by' => 'newest'
                ]);

            if (!$response->successful()) {
                throw new \Exception('Guardian API request failed: ' . $response->body());
            }

            $data = $response->json();
            $articles = $data['response']['results'] ?? [];

            $savedCount = 0;
            $skippedCount = 0;

            foreach ($articles as $articleData) {
                if ($this->saveArticle($articleData, $source)) {
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
            Log::error('Guardian scraping failed', [
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
        $url = $data['webUrl'] ?? '';

        if (Article::where('url', $url)->exists() || empty($url)) {
            return false;
        }

        $fields = $data['fields'] ?? [];
        $category = $this->mapGuardianSection($data['sectionName'] ?? 'general');

        Article::create([
            'title' => $fields['headline'] ?? $data['webTitle'],
            'description' => $fields['trailText'] ?? '',
            'content' => strip_tags($fields['body'] ?? ''),
            'url' => $url,
            'image_url' => $fields['thumbnail'] ?? null,
            'author' => $fields['byline'] ?? null,
            'news_source_id' => $source->id,
            'category_id' => $category?->id,
            'published_at' => Carbon::parse($data['webPublicationDate']),
            'metadata' => [
                'source_api' => 'guardian',
                'section' => $data['sectionName'] ?? null,
                'original_data' => $data
            ]
        ]);

        return true;
    }

    private function mapGuardianSection(string $section): ?Category
    {
        $mapping = [
            'technology' => 'technology',
            'business' => 'business',
            'sport' => 'sports',
            'science' => 'science',
            'culture' => 'entertainment',
            'lifeandstyle' => 'health',
        ];

        $categorySlug = $mapping[strtolower($section)] ?? 'general';
        return Category::where('slug', $categorySlug)->first();
    }
}
