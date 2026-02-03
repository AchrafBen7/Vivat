<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
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
    }

    public function down(): void
    {
        DB::unprepared('DROP TRIGGER IF EXISTS sources_updated_at_trigger');
        DB::unprepared('DROP TRIGGER IF EXISTS articles_updated_at_trigger');
    }
};
