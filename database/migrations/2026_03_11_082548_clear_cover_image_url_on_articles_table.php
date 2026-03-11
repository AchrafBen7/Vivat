<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Vide toutes les URLs de couverture pour que le site utilise automatiquement
     * les photos Unsplash (fallback par catégorie) à l'affichage.
     */
    public function up(): void
    {
        DB::table('articles')->update(['cover_image_url' => null]);
    }

    /**
     * Reverse the migrations (on ne peut pas restaurer les anciennes URLs).
     */
    public function down(): void
    {
        // Rien à restaurer
    }
};
