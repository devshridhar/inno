<?php

namespace App\Http\Middleware;

use App\Models\UserPreference;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserPreferences
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Only apply to authenticated users
        if (!$request->user()) {
            return $next($request);
        }

        $user = $request->user();

        // Check if user has preferences
        if (!$user->userPreferences) {
            // Create default preferences for the user
            UserPreference::create([
                'user_id' => $user->id,
                'preferred_sources' => [],
                'preferred_categories' => [],
                'preferred_authors' => [],
                'blocked_sources' => [],
                'blocked_keywords' => [],
                'language' => 'en',
                'country' => 'us',
                'articles_per_page' => 20,
                'email_notifications' => false,
                'email_frequency' => 'daily',
            ]);

            // Refresh the user relationship to include the new preferences
            $user->load('userPreferences');
        }

        return $next($request);
    }
}