<?php

namespace App\Http\Requests\UserPreference;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePreferenceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'preferred_sources' => 'nullable|array',
            'preferred_sources.*' => 'exists:news_sources,id',
            'preferred_categories' => 'nullable|array',
            'preferred_categories.*' => 'exists:categories,id',
            'preferred_authors' => 'nullable|array',
            'preferred_authors.*' => 'string|max:255',
            'blocked_sources' => 'nullable|array',
            'blocked_sources.*' => 'exists:news_sources,id',
            'blocked_keywords' => 'nullable|array',
            'blocked_keywords.*' => 'string|max:100',
            'language' => 'nullable|string|size:2',
            'country' => 'nullable|string|size:2',
            'articles_per_page' => 'nullable|integer|min:5|max:100',
            'email_notifications' => 'nullable|boolean',
            'email_frequency' => 'nullable|in:daily,weekly,never',
        ];
    }
}
