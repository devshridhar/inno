<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('articles', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('title');
            $table->text('description')->nullable();
            $table->longText('content')->nullable();
            $table->string('url')->unique();
            $table->string('image_url')->nullable();
            $table->string('author')->nullable();
            $table->foreignId('news_source_id')->constrained()->onDelete('cascade');
            $table->foreignId('category_id')->nullable()->constrained()->onDelete('set null');
            $table->timestamp('published_at');
            $table->json('metadata')->nullable(); // Store additional API data
            $table->string('language', 5)->default('en');
            $table->string('country', 2)->default('us');
            $table->integer('word_count')->default(0);
            $table->integer('reading_time_minutes')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            // Indexes for better performance
            $table->index(['published_at', 'is_active']);
            $table->index(['news_source_id', 'published_at']);
            $table->index(['category_id', 'published_at']);
            $table->index('uuid');

            // Full-text search index
            $table->fullText(['title', 'description', 'content']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('articles');
    }
};
