<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('article_sources', function (Blueprint $table) {
            $table->timestamp('used_at')->nullable()->default(null)->change();
        });
    }

    public function down(): void
    {
        Schema::table('article_sources', function (Blueprint $table) {
            $table->timestamp('used_at')->nullable(false)->useCurrent()->change();
        });
    }
};
