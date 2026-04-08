<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('submission_payments', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('submission_id');
            $table->uuid('quote_id');
            $table->uuid('user_id');
            $table->string('stripe_checkout_session_id')->unique()->nullable();
            $table->string('stripe_payment_intent_id')->unique()->nullable();
            $table->string('stripe_client_secret')->nullable();
            $table->unsignedInteger('amount_cents');
            $table->char('currency', 3)->default('eur');
            $table->enum('status', ['pending', 'processing', 'succeeded', 'failed', 'canceled', 'refunded', 'disputed'])->default('pending');
            $table->string('stripe_receipt_url')->nullable();
            $table->string('failure_code')->nullable();
            $table->text('failure_message')->nullable();
            $table->string('idempotency_key')->unique();   // uuid généré avant l'appel Stripe
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('refunded_at')->nullable();
            $table->timestamps();

            $table->foreign('submission_id')->references('id')->on('submissions')->cascadeOnDelete();
            $table->foreign('quote_id')->references('id')->on('publication_quotes')->cascadeOnDelete();
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();

            $table->index(['submission_id', 'status']);
            $table->index('quote_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('submission_payments');
    }
};
