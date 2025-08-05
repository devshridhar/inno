<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_preferences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->json('preferred_sources')->nullable(); // Array of source IDs
            $table->json('preferred_categories')->nullable(); // Array of category IDs
            $table->json('preferred_authors')->nullable(); // Array of author names
            $table->json('blocked_sources')->nullable(); // Array of blocked source IDs
            $table->json('blocked_keywords')->nullable(); // Array of blocked keywords
            $table->string('language', 5)->default('en');
            $table->string('country', 2)->default('us');
            $table->integer('articles_per_page')->default(20);
            $table->boolean('email_notifications')->default(false);
            $table->string('email_frequency')->default('daily'); // daily, weekly, never
            $table->timestamps();

            $table->unique('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_preferences');
    }
};

