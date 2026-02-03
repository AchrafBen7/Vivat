<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('articles', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('excerpt')->nullable();
            $table->longText('content');
            $table->string('meta_title')->nullable();
            $table->string('meta_description')->nullable();
            $table->json('keywords')->nullable();
            $table->foreignUuid('category_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->foreignUuid('cluster_id')->nullable()->constrained('clusters')->nullOnDelete();
            $table->unsignedSmallInteger('reading_time')->default(5);
            $table->enum('status', ['draft', 'review', 'published', 'archived', 'rejected'])->default('draft');
            $table->unsignedTinyInteger('quality_score')->default(0);
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
            $table->index('status');
            $table->index('published_at');
            $table->index('quality_score');
            $table->index(['status', 'published_at'], 'idx_articles_published');
            $table->index(['category_id', 'status'], 'idx_articles_category_status');
            $table->fullText(['title', 'excerpt'], 'ft_articles_search');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('articles');
    }
};
