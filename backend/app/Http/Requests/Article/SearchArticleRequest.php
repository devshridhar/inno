<?php

namespace App\Http\Requests\Article;

use Illuminate\Foundation\Http\FormRequest;

class SearchArticleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'q' => 'nullable|string|max:255',
            'category' => 'nullable|array',
            'category.*' => 'exists:categories,id',
            'source' => 'nullable|array',
            'source.*' => 'exists:news_sources,id',
            'author' => 'nullable|string|max:255',
            'from_date' => 'nullable|date',
            'to_date' => 'nullable|date|after_or_equal:from_date',
            'language' => 'nullable|string|size:2',
            'sort_by' => 'nullable|in:published_at,title,created_at',
            'sort_order' => 'nullable|in:asc,desc',
            'per_page' => 'nullable|integer|min:1|max:100',
        ];
    }

    public function messages(): array
    {
        return [
            'to_date.after_or_equal' => 'End date must be after or equal to start date',
            'category.*.exists' => 'Selected category does not exist',
            'source.*.exists' => 'Selected news source does not exist',
            'per_page.max' => 'Maximum 100 articles per page allowed',
        ];
    }
}
