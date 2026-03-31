<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("
            ALTER TABLE payments
            MODIFY status ENUM('pending', 'paid', 'refunded', 'failed', 'abandoned')
            NOT NULL DEFAULT 'pending'
        ");
    }

    public function down(): void
    {
        DB::statement("UPDATE payments SET status = 'failed' WHERE status = 'abandoned'");

        DB::statement("
            ALTER TABLE payments
            MODIFY status ENUM('pending', 'paid', 'refunded', 'failed')
            NOT NULL DEFAULT 'pending'
        ");
    }
};
