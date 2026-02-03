<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('article_sources', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('article_id')->nullable()->constrained('articles')->cascadeOnDelete();
            $table->foreignUuid('rss_item_id')->nullable()->constrained('rss_items')->nullOnDelete();
            $table->foreignUuid('source_id')->nullable()->constrained('sources')->nullOnDelete();
            $table->text('url');
            $table->timestamp('used_at')->useCurrent();
            $table->index('article_id');
            $table->index('rss_item_id');
            $table->unique(['article_id', 'rss_item_id'], 'uk_article_rss_item');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('article_sources');
    }
};
