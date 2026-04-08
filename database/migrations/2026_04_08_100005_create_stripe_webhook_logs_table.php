<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stripe_webhook_logs', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('stripe_event_id')->unique();  // clé d'idempotence
            $table->string('type');
            $table->json('payload')->nullable();
            $table->timestamp('processed_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stripe_webhook_logs');
    }
};
