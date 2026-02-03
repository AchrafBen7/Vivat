<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('category_templates', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('category_id')->nullable()->unique()->constrained('categories')->cascadeOnDelete();
            $table->string('tone', 50)->default('professional');
            $table->string('structure', 50)->default('standard');
            $table->unsignedSmallInteger('min_word_count')->default(900);
            $table->unsignedSmallInteger('max_word_count')->default(2000);
            $table->text('style_notes')->nullable();
            $table->text('seo_rules')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('category_templates');
    }
};
