<?php

namespace App\Jobs;

use App\Models\NewsSource;
use App\Services\NewsScrapers\GuardianService;
use App\Services\NewsScrapers\NewsAPIService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ScrapeNewsFromSource implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 300; // 5 minutes
    public int $tries = 3;

    public function __construct(
        private NewsSource $newsSource
    ) {}

    public function handle(): void
    {
        Log::info('Starting news scraping', ['source' => $this->newsSource->name]);

        $service = $this->getScrapingService();

        if (!$service) {
            Log::warning('No scraping service available for source', [
                'source' => $this->newsSource->name
            ]);
            return;
        }

        $result = $service->scrapeArticles($this->newsSource);

        // Update source scraping stats
        $this->newsSource->updateScrapeStats($result);

        Log::info('News scraping completed', [
            'source' => $this->newsSource->name,
            'result' => $result
        ]);
    }

    private function getScrapingService()
    {
        return match ($this->newsSource->slug) {
            'newsapi-general', 'newsapi' => new NewsAPIService(),
            'the-guardian' => new GuardianService(),
            default => null,
        };
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('News scraping job failed', [
            'source' => $this->newsSource->name,
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString()
        ]);
    }
}
