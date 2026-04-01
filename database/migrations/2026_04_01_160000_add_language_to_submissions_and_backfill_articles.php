<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('submissions', function (Blueprint $table) {
            $table->string('language', 10)->default('fr')->after('category_id');
            $table->index('language');
        });

        DB::table('submissions')
            ->whereNull('language')
            ->update(['language' => 'fr']);

        DB::table('articles')
            ->whereNull('language')
            ->update(['language' => 'fr']);
    }

    public function down(): void
    {
        DB::table('articles')
            ->where('language', 'fr')
            ->update(['language' => null]);

        Schema::table('submissions', function (Blueprint $table) {
            $table->dropIndex(['language']);
            $table->dropColumn('language');
        });
    }
};
