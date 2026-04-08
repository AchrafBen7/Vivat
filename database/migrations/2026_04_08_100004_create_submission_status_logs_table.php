<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('submission_status_logs', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('submission_id');
            $table->string('from_status')->nullable();
            $table->string('to_status');
            $table->uuid('triggered_by')->nullable();  // null = système ou webhook
            $table->enum('trigger_source', ['author', 'moderator', 'system', 'stripe_webhook'])->default('system');
            $table->text('reason')->nullable();
            $table->json('metadata')->nullable();      // ex: stripe_event_id, quote_id
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('submission_id')->references('id')->on('submissions')->cascadeOnDelete();
            $table->foreign('triggered_by')->references('id')->on('users')->nullOnDelete();

            $table->index('submission_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('submission_status_logs');
    }
};
