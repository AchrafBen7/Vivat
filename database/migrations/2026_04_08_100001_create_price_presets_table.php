<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('price_presets', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('label');
            $table->text('description')->nullable();
            $table->unsignedInteger('amount_cents');   // en centimes : 2900 = 29,00 €
            $table->char('currency', 3)->default('eur');
            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });

        // Presets par défaut
        DB::table('price_presets')->insert([
            ['id' => \Illuminate\Support\Str::uuid(), 'label' => 'Article bref', 'description' => 'Article court (moins de 500 mots)', 'amount_cents' => 1500, 'currency' => 'eur', 'is_active' => 1, 'sort_order' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['id' => \Illuminate\Support\Str::uuid(), 'label' => 'Article standard', 'description' => 'Article de fond (500 à 1 500 mots)', 'amount_cents' => 2900, 'currency' => 'eur', 'is_active' => 1, 'sort_order' => 2, 'created_at' => now(), 'updated_at' => now()],
            ['id' => \Illuminate\Support\Str::uuid(), 'label' => 'Article premium', 'description' => 'Article long ou exclusif (plus de 1 500 mots)', 'amount_cents' => 5900, 'currency' => 'eur', 'is_active' => 1, 'sort_order' => 3, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('price_presets');
    }
};
