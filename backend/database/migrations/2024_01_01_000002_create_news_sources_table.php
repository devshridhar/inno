<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('news_sources', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('url');
            $table->string('api_endpoint')->nullable();
            $table->string('api_key_required')->default('yes');
            $table->json('api_config')->nullable();
            $table->string('language', 5)->default('en');
            $table->string('country', 2)->default('us');
            $table->string('logo_url')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('scrape_interval_minutes')->default(120);
            $table->timestamp('last_scraped_at')->nullable();
            $table->json('scrape_stats')->nullable();
            $table->timestamps();

            $table->index(['is_active', 'last_scraped_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('news_sources');
    }
};
