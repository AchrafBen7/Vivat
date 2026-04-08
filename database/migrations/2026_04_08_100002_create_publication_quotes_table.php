<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('publication_quotes', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('submission_id');
            $table->uuid('proposed_by');                  // modérateur
            $table->uuid('price_preset_id')->nullable();  // si preset choisi
            $table->unsignedInteger('amount_cents');       // toujours en centimes
            $table->char('currency', 3)->default('eur');
            $table->enum('status', ['pending', 'sent', 'accepted', 'expired', 'canceled'])->default('pending');
            $table->text('note_to_author')->nullable();    // message visible par le rédacteur
            $table->timestamp('expires_at');               // TTL : envoi + 7 jours
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('accepted_at')->nullable();
            $table->timestamps();

            $table->foreign('submission_id')->references('id')->on('submissions')->cascadeOnDelete();
            $table->foreign('proposed_by')->references('id')->on('users')->restrictOnDelete();
            $table->foreign('price_preset_id')->references('id')->on('price_presets')->nullOnDelete();

            $table->index('submission_id');
            $table->index('status');
            $table->index('expires_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('publication_quotes');
    }
};
