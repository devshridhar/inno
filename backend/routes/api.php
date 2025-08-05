<?php

use App\Http\Controllers\Api\ArticleController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\NewsSourceController;
use App\Http\Controllers\Api\SearchController;
use App\Http\Controllers\Api\UserPreferenceController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Health check
Route::get('/health', function () {
    return response()->json([
        'status' => 'ok',
        'timestamp' => now(),
        'service' => 'News Aggregator API'
    ]);
});

// Public routes
Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
});

// Public article routes
Route::prefix('articles')->group(function () {
    Route::get('/', [ArticleController::class, 'index']);
    Route::get('/{uuid}', [ArticleController::class, 'show']);
});

// Public search
Route::get('/search', [SearchController::class, 'search']);
Route::get('/search/suggestions', [SearchController::class, 'suggestions']);

// Public categories and sources
Route::get('/categories', [CategoryController::class, 'index']);
Route::get('/categories/{slug}', [CategoryController::class, 'show']);
Route::get('/sources', [NewsSourceController::class, 'index']);
Route::get('/sources/{slug}', [NewsSourceController::class, 'show']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    // Auth routes
    Route::prefix('auth')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/me', [AuthController::class, 'me']);
    });

    // User preferences
    Route::prefix('preferences')->group(function () {
        Route::get('/', [UserPreferenceController::class, 'show']);
        Route::put('/', [UserPreferenceController::class, 'update']);
        Route::post('/sources', [UserPreferenceController::class, 'addPreferredSource']);
        Route::delete('/sources', [UserPreferenceController::class, 'removePreferredSource']);
    });

    // User-specific article routes
    Route::prefix('articles')->group(function () {
        Route::post('/{uuid}/bookmark', [ArticleController::class, 'bookmark']);
        Route::delete('/{uuid}/bookmark', [ArticleController::class, 'removeBookmark']);
        Route::get('/bookmarks', [ArticleController::class, 'bookmarks']);
    });
});