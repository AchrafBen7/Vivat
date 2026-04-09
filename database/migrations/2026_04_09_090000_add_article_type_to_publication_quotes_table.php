<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('publication_quotes', function (Blueprint $table): void {
            $table->string('article_type', 20)->default('standard')->after('currency');
        });
    }

    public function down(): void
    {
        Schema::table('publication_quotes', function (Blueprint $table): void {
            $table->dropColumn('article_type');
        });
    }
};
