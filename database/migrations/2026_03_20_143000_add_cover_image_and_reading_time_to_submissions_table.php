<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('submissions', function (Blueprint $table) {
            $table->unsignedSmallInteger('reading_time')->nullable()->after('category_id');
            $table->string('cover_image_path', 500)->nullable()->after('payment_id');
        });
    }

    public function down(): void
    {
        Schema::table('submissions', function (Blueprint $table) {
            $table->dropColumn(['reading_time', 'cover_image_path']);
        });
    }
};
