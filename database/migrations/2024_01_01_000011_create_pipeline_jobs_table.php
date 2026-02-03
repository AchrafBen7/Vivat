<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pipeline_jobs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->enum('job_type', ['fetch_rss', 'enrich', 'cluster', 'generate', 'publish', 'cleanup']);
            $table->enum('status', ['pending', 'running', 'completed', 'failed'])->default('pending');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->text('error_message')->nullable();
            $table->json('metadata')->nullable();
            $table->unsignedTinyInteger('retry_count')->default(0);
            $table->timestamp('created_at')->useCurrent();
            $table->index('job_type');
            $table->index('status');
            $table->index(['status', 'created_at'], 'idx_jobs_pending');
            $table->index(['job_type', 'status'], 'idx_jobs_type_status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pipeline_jobs');
    }
};
