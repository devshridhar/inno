<?php
// ./backend/app/Jobs/ProcessArticleContent.php

namespace App\Jobs;

use App\Models\Article;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ProcessArticleContent implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 120; // 2 minutes
    public int $tries = 2;

    public function __construct(
        private Article $article
    ) {}

    public function handle(): void
    {
        Log::info('Processing article content', [
            'article_id' => $this->article->id,
            'title' => $this->article->title
        ]);

        try {
            // Extract and clean content if it's HTML
            if ($this->article->content && $this->containsHtml($this->article->content)) {
                $cleanContent = $this->cleanHtmlContent($this->article->content);
                $this->article->update(['content' => $cleanContent]);
            }

            // Generate excerpt if missing
            if (empty($this->article->description) && !empty($this->article->content)) {
                $excerpt = $this->generateExcerpt($this->article->content);
                $this->article->update(['description' => $excerpt]);
            }

            // Calculate reading time and word count
            $this->calculateReadingMetrics();

            // Extract and save keywords
            $keywords = $this->extractKeywords($this->article->content ?? $this->article->description ?? '');

            // Update metadata
            $metadata = $this->article->metadata ?? [];
            $metadata['processed_at'] = now()->toISOString();
            $metadata['keywords'] = $keywords;
            $metadata['content_length'] = strlen($this->article->content ?? '');

            $this->article->update(['metadata' => $metadata]);

            Log::info('Article content processed successfully', [
                'article_id' => $this->article->id,
                'word_count' => $this->article->word_count,
                'reading_time' => $this->article->reading_time_minutes
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to process article content', [
                'article_id' => $this->article->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            throw $e; // Re-throw to trigger retry mechanism
        }
    }

    private function containsHtml(string $content): bool
    {
        return $content !== strip_tags($content);
    }

    private function cleanHtmlContent(string $content): string
    {
        // Remove HTML tags but preserve formatting
        $content = strip_tags($content, '<p><br><strong><em><ul><ol><li>');

        // Convert HTML entities
        $content = html_entity_decode($content, ENT_QUOTES, 'UTF-8');

        // Clean up extra whitespace
        $content = preg_replace('/\s+/', ' ', $content);
        $content = trim($content);

        return $content;
    }

    private function generateExcerpt(string $content): string
    {
        $cleanContent = strip_tags($content);
        return Str::limit($cleanContent, 200);
    }

    private function calculateReadingMetrics(): void
    {
        $content = $this->article->content ?? $this->article->description ?? '';
        $wordCount = str_word_count(strip_tags($content));
        $readingTime = max(1, ceil($wordCount / 200)); // 200 words per minute

        $this->article->update([
            'word_count' => $wordCount,
            'reading_time_minutes' => $readingTime
        ]);
    }

    private function extractKeywords(string $content): array
    {
        if (empty($content)) {
            return [];
        }

        $content = strtolower(strip_tags($content));

        // Remove common stop words
        $stopWords = [
            'the', 'a', 'an', 'and', 'or', 'but', 'in', 'on', 'at', 'to', 'for',
            'of', 'with', 'by', 'is', 'are', 'was', 'were', 'be', 'been', 'have',
            'has', 'had', 'do', 'does', 'did', 'will', 'would', 'could', 'should',
            'this', 'that', 'these', 'those', 'i', 'you', 'he', 'she', 'it', 'we', 'they'
        ];

        // Extract words
        preg_match_all('/\b[a-z]{3,}\b/', $content, $matches);
        $words = $matches[0];

        // Filter stop words
        $words = array_filter($words, fn($word) => !in_array($word, $stopWords));

        // Count frequency and get top keywords
        $wordCount = array_count_values($words);
        arsort($wordCount);

        return array_keys(array_slice($wordCount, 0, 10));
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('Article content processing job failed', [
            'article_id' => $this->article->id,
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString()
        ]);
    }
}