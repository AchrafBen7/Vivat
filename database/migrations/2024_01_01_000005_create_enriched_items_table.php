<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('enriched_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('rss_item_id')->nullable()->unique()->constrained('rss_items')->cascadeOnDelete();
            $table->text('lead')->nullable();
            $table->json('headings')->nullable();
            $table->json('key_points')->nullable();
            $table->longText('extracted_text')->nullable();
            $table->string('extraction_method', 50)->default('readability');
            $table->unsignedTinyInteger('quality_score')->default(0);
            $table->timestamp('enriched_at')->useCurrent();
            $table->index('quality_score');
            $table->index('extraction_method');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('enriched_items');
    }
};
