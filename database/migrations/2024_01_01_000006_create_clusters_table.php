<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clusters', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('category_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->string('label');
            $table->json('keywords')->nullable();
            $table->enum('status', ['pending', 'processing', 'generated', 'failed'])->default('pending');
            $table->timestamp('created_at')->useCurrent();
            $table->index('status');
            $table->index(['category_id', 'status'], 'idx_clusters_category_status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clusters');
    }
};
