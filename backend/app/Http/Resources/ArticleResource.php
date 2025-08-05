<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ArticleResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'uuid' => $this->uuid,
            'title' => $this->title,
            'description' => $this->description,
            'excerpt' => $this->excerpt,
            'content' => $this->when($request->routeIs('articles.show'), $this->content),
            'url' => $this->url,
            'image_url' => $this->image_url,
            'author' => $this->author,
            'published_at' => $this->published_at,
            'reading_time' => $this->reading_time,
            'word_count' => $this->word_count,
            'language' => $this->language,
            'country' => $this->country,
            'news_source' => new NewsSourceResource($this->whenLoaded('newsSource')),
            'category' => new CategoryResource($this->whenLoaded('category')),
            'is_bookmarked' => $this->when(
                $request->user(),
                fn() => $this->bookmarkedByUsers()->where('user_id', $request->user()->id)->exists()
            ),
            'created_at' => $this->created_at,
        ];
    }
}
