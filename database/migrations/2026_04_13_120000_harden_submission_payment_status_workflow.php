<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("
            ALTER TABLE submissions
            MODIFY COLUMN status ENUM(
                'draft',
                'pending',
                'submitted',
                'under_review',
                'changes_requested',
                'rejected',
                'price_proposed',
                'awaiting_payment',
                'payment_pending',
                'payment_succeeded',
                'payment_failed',
                'payment_expired',
                'payment_canceled',
                'payment_refunded',
                'approved',
                'published'
            ) NOT NULL DEFAULT 'draft'
        ");

        DB::statement("
            ALTER TABLE submission_payments
            MODIFY COLUMN status ENUM(
                'pending',
                'processing',
                'succeeded',
                'failed',
                'canceled',
                'expired',
                'refunded',
                'disputed'
            ) NOT NULL DEFAULT 'pending'
        ");

        Schema::table('submission_payments', function ($table): void {
            $table->string('stripe_refund_id')->nullable()->after('stripe_receipt_url');
            $table->text('refund_reason')->nullable()->after('failure_message');
            $table->uuid('refunded_by')->nullable()->after('refund_reason');
            $table->timestamp('disputed_at')->nullable()->after('refunded_at');
            $table->string('dispute_reason')->nullable()->after('disputed_at');
        });
    }

    public function down(): void
    {
        Schema::table('submission_payments', function ($table): void {
            $table->dropColumn([
                'stripe_refund_id',
                'refund_reason',
                'refunded_by',
                'disputed_at',
                'dispute_reason',
            ]);
        });

        DB::statement("
            ALTER TABLE submission_payments
            MODIFY COLUMN status ENUM(
                'pending',
                'processing',
                'succeeded',
                'failed',
                'canceled',
                'refunded',
                'disputed'
            ) NOT NULL DEFAULT 'pending'
        ");

        DB::statement("
            ALTER TABLE submissions
            MODIFY COLUMN status ENUM(
                'draft',
                'pending',
                'submitted',
                'under_review',
                'changes_requested',
                'rejected',
                'price_proposed',
                'awaiting_payment',
                'payment_pending',
                'payment_succeeded',
                'payment_failed',
                'payment_expired',
                'payment_canceled',
                'approved',
                'published'
            ) NOT NULL DEFAULT 'draft'
        ");
    }
};

