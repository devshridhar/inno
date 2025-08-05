<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Article extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid', 'title', 'description', 'content', 'url', 'image_url',
        'author', 'news_source_id', 'category_id', 'published_at',
        'metadata', 'language', 'country', 'word_count',
        'reading_time_minutes', 'is_active'
    ];

    protected $casts = [
        'published_at' => 'datetime',
        'metadata' => 'array',
        'is_active' => 'boolean',
    ];

    // Boot method to generate UUID
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($article) {
            if (empty($article->uuid)) {
                $article->uuid = Str::uuid();
            }

            // Calculate reading time (average 200 words per minute)
            if ($article->content) {
                $article->word_count = str_word_count(strip_tags($article->content));
                $article->reading_time_minutes = max(1, ceil($article->word_count / 200));
            }
        });
    }

    // Relationships
    public function newsSource()
    {
        return $this->belongsTo(NewsSource::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function userInteractions()
    {
        return $this->hasMany(UserArticleInteraction::class);
    }

    public function bookmarkedByUsers()
    {
        return $this->belongsToMany(User::class, 'user_article_interactions')
            ->wherePivot('interaction_type', 'bookmark');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopePublished($query)
    {
        return $query->where('published_at', '<=', now());
    }

    public function scopeRecent($query, $days = 7)
    {
        return $query->where('published_at', '>=', now()->subDays($days));
    }

    public function scopeByCategory($query, $categoryId)
    {
        return $query->where('category_id', $categoryId);
    }

    public function scopeBySource($query, $sourceId)
    {
        return $query->where('news_source_id', $sourceId);
    }

    public function scopeSearch($query, $search)
    {
        return $query->whereFullText(['title', 'description', 'content'], $search);
    }

    // Accessors
    public function getExcerptAttribute(): string
    {
        return Str::limit($this->description ?: strip_tags($this->content), 150);
    }

    public function getReadingTimeAttribute(): string
    {
        return $this->reading_time_minutes . ' min read';
    }
}