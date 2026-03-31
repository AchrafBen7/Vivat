<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('submissions', function (Blueprint $table) {
            $table->foreignUuid('published_article_id')->nullable()->after('payment_id')->constrained('articles')->nullOnDelete();
            $table->timestamp('depublication_requested_at')->nullable()->after('published_article_id');
            $table->text('depublication_reason')->nullable()->after('depublication_requested_at');

            $table->index(['user_id', 'published_article_id'], 'submissions_user_published_article_idx');
        });
    }

    public function down(): void
    {
        Schema::table('submissions', function (Blueprint $table) {
            $table->dropIndex('submissions_user_published_article_idx');
            $table->dropConstrainedForeignId('published_article_id');
            $table->dropColumn(['depublication_requested_at', 'depublication_reason']);
        });
    }
};
