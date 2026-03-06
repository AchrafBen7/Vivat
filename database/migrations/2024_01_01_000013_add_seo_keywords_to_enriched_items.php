<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('enriched_items', function (Blueprint $table) {
            $table->json('seo_keywords')->nullable()->after('key_points');
            $table->unsignedTinyInteger('seo_score')->default(0)->after('quality_score');
            $table->string('primary_topic', 255)->nullable()->after('seo_keywords');
        });
    }

    public function down(): void
    {
        Schema::table('enriched_items', function (Blueprint $table) {
            $table->dropColumn(['seo_keywords', 'seo_score', 'primary_topic']);
        });
    }
};
