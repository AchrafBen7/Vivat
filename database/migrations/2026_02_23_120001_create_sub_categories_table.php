<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sub_categories', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('category_id')->constrained('categories')->cascadeOnDelete();
            $table->string('name');
            $table->string('slug', 255);
            $table->text('description')->nullable();
            $table->unsignedTinyInteger('order')->default(1); // 1-5 pour affichage
            $table->string('image_url', 500)->nullable();
            $table->string('video_url', 500)->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->unique(['category_id', 'slug']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sub_categories');
    }
};
