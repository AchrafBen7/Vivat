<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // MySQL ENUM ne supporte pas ->change() proprement avec Doctrine
        // On utilise une instruction ALTER TABLE brute
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

        Schema::table('submissions', function (Blueprint $table): void {
            $table->timestamp('submitted_at')->nullable()->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('submissions', function (Blueprint $table): void {
            $table->dropColumn('submitted_at');
        });

        DB::statement("
            ALTER TABLE submissions
            MODIFY COLUMN status ENUM('draft','pending','approved','rejected') NOT NULL DEFAULT 'draft'
        ");
    }
};
