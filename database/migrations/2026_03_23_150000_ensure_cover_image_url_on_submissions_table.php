<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Corrige le schéma submissions si cover_image_url est manquante
     * (ex: dump créé avant la migration de renommage, ou migrations partielles).
     */
    public function up(): void
    {
        if (Schema::hasColumn('submissions', 'cover_image_url')) {
            return;
        }

        if (Schema::hasColumn('submissions', 'cover_image_path')) {
            DB::statement('ALTER TABLE submissions CHANGE cover_image_path cover_image_url VARCHAR(500) NULL');
        } else {
            Schema::table('submissions', function (Blueprint $table) {
                $table->string('cover_image_url', 500)->nullable()->after('payment_id');
            });
        }
    }

    public function down(): void
    {
        // Ne pas annuler pour éviter de casser des environnements
    }
};
