
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserArticleInteraction extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'article_id', 'interaction_type',
        'interacted_at', 'metadata'
    ];

    protected $casts = [
        'interacted_at' => 'datetime',
        'metadata' => 'array',
    ];

    // Boot method
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($interaction) {
            if (empty($interaction->interacted_at)) {
                $interaction->interacted_at = now();
            }
        });
    }

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function article()
    {
        return $this->belongsTo(Article::class);
    }

    // Scopes
    public function scopeByType($query, $type)
    {
        return $query->where('interaction_type', $type);
    }

    public function scopeRecent($query, $days = 30)
    {
        return $query->where('interacted_at', '>=', now()->subDays($days));
    }
}