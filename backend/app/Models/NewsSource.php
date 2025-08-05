<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NewsSource extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'slug', 'description', 'url', 'api_endpoint',
        'api_key_required', 'api_config', 'language', 'country',
        'logo_url', 'is_active', 'scrape_interval_minutes',
        'last_scraped_at', 'scrape_stats'
    ];

    protected $casts = [
        'api_config' => 'array',
        'scrape_stats' => 'array',
        'is_active' => 'boolean',
        'last_scraped_at' => 'datetime',
    ];

    // Relationships
    public function articles()
    {
        return $this->hasMany(Article::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeNeedsScraping($query)
    {
        return $query->active()
            ->where(function ($q) {
                $q->whereNull('last_scraped_at')
                    ->orWhereRaw('last_scraped_at < NOW() - INTERVAL \'1 minute\' * scrape_interval_minutes');
            });
    }

    // Methods
    public function updateScrapeStats(array $stats): void
    {
        $this->update([
            'last_scraped_at' => now(),
            'scrape_stats' => array_merge($this->scrape_stats ?? [], $stats)
        ]);
    }
}