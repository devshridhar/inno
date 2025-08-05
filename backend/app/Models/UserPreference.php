<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserPreference extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'preferred_sources', 'preferred_categories',
        'preferred_authors', 'blocked_sources', 'blocked_keywords',
        'language', 'country', 'articles_per_page',
        'email_notifications', 'email_frequency'
    ];

    protected $casts = [
        'preferred_sources' => 'array',
        'preferred_categories' => 'array',
        'preferred_authors' => 'array',
        'blocked_sources' => 'array',
        'blocked_keywords' => 'array',
        'email_notifications' => 'boolean',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Methods
    public function addPreferredSource(int $sourceId): void
    {
        $sources = $this->preferred_sources ?? [];
        if (!in_array($sourceId, $sources)) {
            $sources[] = $sourceId;
            $this->update(['preferred_sources' => $sources]);
        }
    }

    public function removePreferredSource(int $sourceId): void
    {
        $sources = $this->preferred_sources ?? [];
        $this->update(['preferred_sources' => array_values(array_diff($sources, [$sourceId]))]);
    }
}
