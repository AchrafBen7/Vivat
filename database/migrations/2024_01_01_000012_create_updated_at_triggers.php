<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }
        try {
            DB::unprepared('
                CREATE TRIGGER sources_updated_at_trigger
                BEFORE UPDATE ON sources
                FOR EACH ROW
                SET NEW.updated_at = NOW()
            ');
            DB::unprepared('
                CREATE TRIGGER articles_updated_at_trigger
                BEFORE UPDATE ON articles
                FOR EACH ROW
                SET NEW.updated_at = NOW()
            ');
        } catch (\Exception $e) {
            // Triggers require SUPER privilege when binary logging is enabled.
            // Eloquent handles updated_at automatically, so this is non-critical.
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }
        DB::unprepared('DROP TRIGGER IF EXISTS sources_updated_at_trigger');
        DB::unprepared('DROP TRIGGER IF EXISTS articles_updated_at_trigger');
    }
};
