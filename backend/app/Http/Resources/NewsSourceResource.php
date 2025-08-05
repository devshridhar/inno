<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NewsSourceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'url' => $this->url,
            'logo_url' => $this->logo_url,
            'language' => $this->language,
            'country' => $this->country,
            'articles_count' => $this->when(isset($this->articles_count), $this->articles_count),
            'last_scraped_at' => $this->last_scraped_at,
            'is_active' => $this->is_active,
        ];
    }
}
