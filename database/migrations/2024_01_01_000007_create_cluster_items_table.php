<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cluster_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('cluster_id')->nullable()->constrained('clusters')->cascadeOnDelete();
            $table->foreignUuid('rss_item_id')->nullable()->constrained('rss_items')->cascadeOnDelete();
            $table->unique(['cluster_id', 'rss_item_id'], 'uk_cluster_item');
            $table->index('rss_item_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cluster_items');
    }
};
