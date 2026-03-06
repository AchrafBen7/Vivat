<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reading_histories', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->nullable()->constrained('users')->cascadeOnDelete();
            $table->string('session_id', 100)->nullable()->index(); // cookie-based tracking
            $table->foreignUuid('article_id')->constrained('articles')->cascadeOnDelete();
            $table->unsignedTinyInteger('progress')->default(0); // 0-100 percentage
            $table->timestamp('read_at')->useCurrent();
            $table->timestamps();

            $table->index(['user_id', 'article_id']);
            $table->index(['session_id', 'article_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reading_histories');
    }
};
