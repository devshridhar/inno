<?php

namespace Database\Seeders;

use App\Models\NewsSource;
use Illuminate\Database\Seeder;

class NewsSourceSeeder extends Seeder
{
    public function run(): void
    {
        $sources = [
            [
                'name' => 'NewsAPI General',
                'slug' => 'newsapi-general',
                'description' => 'General news from NewsAPI',
                'url' => 'https://newsapi.org',
                'api_endpoint' => 'https://newsapi.org/v2/top-headlines',
                'api_config' => ['country' => 'us', 'category' => 'general'],
                'is_active' => true,
                'scrape_interval_minutes' => 120,
            ],
            [
                'name' => 'The Guardian',
                'slug' => 'the-guardian',
                'description' => 'British daily newspaper',
                'url' => 'https://theguardian.com',
                'api_endpoint' => 'https://content.guardianapis.com/search',
                'api_config' => ['show-fields' => 'headline,trailText,body,thumbnail,byline'],
                'is_active' => true,
                'scrape_interval_minutes' => 180,
            ],
            [
                'name' => 'BBC News',
                'slug' => 'bbc-news',
                'description' => 'British Broadcasting Corporation',
                'url' => 'https://bbc.com',
                'api_endpoint' => 'https://newsapi.org/v2/top-headlines',
                'api_config' => ['sources' => 'bbc-news'],
                'is_active' => true,
                'scrape_interval_minutes' => 120,
            ],
        ];

        foreach ($sources as $sourceData) {
            NewsSource::updateOrCreate(
                ['slug' => $sourceData['slug']],
                $sourceData
            );
        }
    }
}
