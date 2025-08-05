<?php

namespace App\Console\Commands;

use App\Jobs\ScrapeNewsFromSource;
use App\Models\NewsSource;
use Illuminate\Console\Command;

class ScrapeNews extends Command
{
    protected $signature = 'news:scrape {--source= : Specific source slug to scrape}';
    protected $description = 'Scrape news articles from configured sources';

    public function handle(): int
    {
        $this->info('Starting news scraping process...');

        $query = NewsSource::active();

        if ($this->option('source')) {
            $query->where('slug', $this->option('source'));
        } else {
            $query->needsScraping();
        }

        $sources = $query->get();

        if ($sources->isEmpty()) {
            $this->info('No sources need scraping at this time.');
            return Command::SUCCESS;
        }

        $this->info("Found {$sources->count()} sources to scrape.");

        foreach ($sources as $source) {
            $this->info("Queuing scraping job for: {$source->name}");
            ScrapeNewsFromSource::dispatch($source);
        }

        $this->info('All scraping jobs have been queued.');
        return Command::SUCCESS;
    }
}
