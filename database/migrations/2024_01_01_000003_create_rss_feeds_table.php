<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rss_feeds', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('source_id')->nullable()->constrained('sources')->nullOnDelete();
            $table->foreignUuid('category_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->text('feed_url');
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_fetched_at')->nullable();
            $table->unsignedSmallInteger('fetch_interval_minutes')->default(30);
            $table->timestamp('created_at')->useCurrent();
            $table->index('is_active');
            $table->index('last_fetched_at');
            $table->index(['is_active', 'last_fetched_at'], 'idx_feeds_due_fetch');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rss_feeds');
    }
};
