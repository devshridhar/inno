<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_article_interactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('article_id')->constrained()->onDelete('cascade');
            $table->enum('interaction_type', ['view', 'like', 'bookmark', 'share'])->default('view');
            $table->timestamp('interacted_at');
            $table->json('metadata')->nullable(); // Store additional interaction data
            $table->timestamps();

            // Composite indexes
            $table->index(['user_id', 'interaction_type', 'interacted_at']);
            $table->index(['article_id', 'interaction_type']);
            $table->unique(['user_id', 'article_id', 'interaction_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_article_interactions');
    }
};
