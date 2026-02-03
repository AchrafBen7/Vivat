<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rss_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('rss_feed_id')->nullable()->constrained('rss_feeds')->nullOnDelete();
            $table->foreignUuid('category_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->string('guid')->nullable();
            $table->string('title');
            $table->text('description')->nullable();
            $table->text('url');
            $table->timestamp('published_at')->nullable();
            $table->timestamp('fetched_at')->useCurrent();
            $table->enum('status', ['new', 'enriching', 'enriched', 'failed', 'ignored', 'used'])->default('new');
            $table->string('dedup_hash', 64)->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->unique('dedup_hash');
            $table->index('status');
            $table->index('published_at');
            $table->index('fetched_at');
            $table->index(['status', 'fetched_at'], 'idx_items_processing');
            $table->fullText(['title', 'description'], 'ft_items_content');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rss_items');
    }
};
