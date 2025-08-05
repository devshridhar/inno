<?php

return [
    'apis' => [
        'newsapi' => [
            'key' => env('NEWS_API_KEY'),
            'url' => env('NEWS_API_URL', 'https://newsapi.org/v2'),
            'endpoints' => [
                'top_headlines' => '/top-headlines',
                'everything' => '/everything',
                'sources' => '/sources',
            ],
            'rate_limit' => 1000, // requests per day
        ],

        'guardian' => [
            'key' => env('GUARDIAN_API_KEY'),
            'url' => env('GUARDIAN_API_URL', 'https://content.guardianapis.com'),
            'endpoints' => [
                'search' => '/search',
                'sections' => '/sections',
                'tags' => '/tags',
            ],
            'rate_limit' => 5000, // requests per day
        ],

        'nyt' => [
            'key' => env('NYT_API_KEY'),
            'url' => env('NYT_API_URL', 'https://api.nytimes.com/svc'),
            'endpoints' => [
                'top_stories' => '/topstories/v2',
                'archive' => '/archive/v1',
                'search' => '/search/v2/articlesearch.json',
            ],
            'rate_limit' => 4000, // requests per day
        ],
    ],

    'scraping' => [
        'interval_hours' => env('SCRAPE_INTERVAL_HOURS', 2),
        'max_articles_per_source' => env('MAX_ARTICLES_PER_SOURCE', 100),
        'retention_days' => env('ARTICLE_RETENTION_DAYS', 30),
        'timeout_seconds' => 30,
        'retry_attempts' => 3,
    ],

    'categories' => [
        'business' => ['name' => 'Business', 'color' => '#10B981'],
        'technology' => ['name' => 'Technology', 'color' => '#3B82F6'],
        'sports' => ['name' => 'Sports', 'color' => '#F59E0B'],
        'entertainment' => ['name' => 'Entertainment', 'color' => '#8B5CF6'],
        'health' => ['name' => 'Health', 'color' => '#EF4444'],
        'science' => ['name' => 'Science', 'color' => '#06B6D4'],
        'general' => ['name' => 'General', 'color' => '#6B7280'],
    ],
];
