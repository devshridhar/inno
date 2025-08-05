<?php

namespace App\Console\Commands;

use App\Models\Article;
use Illuminate\Console\Command;

class CleanupOldArticles extends Command
{
    protected $signature = 'news:cleanup {--days=30 : Days to keep articles}';
    protected $description = 'Remove old articles from the database';

    public function handle(): int
    {
        $days = $this->option('days');
        $cutoffDate = now()->subDays($days);

        $this->info("Cleaning up articles older than {$days} days (before {$cutoffDate})...");

        $count = Article::where('published_at', '<', $cutoffDate)->count();

        if ($count === 0) {
            $this->info('No old articles to clean up.');
            return Command::SUCCESS;
        }

        if ($this->confirm("This will delete {$count} articles. Continue?")) {
            $deleted = Article::where('published_at', '<', $cutoffDate)->delete();
            $this->info("Successfully deleted {$deleted} old articles.");
        } else {
            $this->info('Cleanup cancelled.');
        }

        return Command::SUCCESS;
    }
}
