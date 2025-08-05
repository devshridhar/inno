<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UserPreference\UpdatePreferenceRequest;
use App\Models\UserPreference;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserPreferenceController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        $preferences = $request->user()->userPreferences;

        if (!$preferences) {
            $preferences = UserPreference::create([
                'user_id' => $request->user()->id,
            ]);
        }

        return response()->json([
            'preferences' => $preferences
        ]);
    }

    public function update(UpdatePreferenceRequest $request): JsonResponse
    {
        $preferences = $request->user()->userPreferences;

        if (!$preferences) {
            $preferences = new UserPreference(['user_id' => $request->user()->id]);
        }

        $preferences->fill($request->validated());
        $preferences->save();

        return response()->json([
            'message' => 'Preferences updated successfully',
            'preferences' => $preferences
        ]);
    }

    public function addPreferredSource(Request $request): JsonResponse
    {
        $request->validate(['source_id' => 'required|exists:news_sources,id']);

        $preferences = $request->user()->userPreferences;
        $preferences->addPreferredSource($request->source_id);

        return response()->json([
            'message' => 'Source added to preferences'
        ]);
    }

    public function removePreferredSource(Request $request): JsonResponse
    {
        $request->validate(['source_id' => 'required|exists:news_sources,id']);

        $preferences = $request->user()->userPreferences;
        $preferences->removePreferredSource($request->source_id);

        return response()->json([
            'message' => 'Source removed from preferences'
        ]);
    }
}
