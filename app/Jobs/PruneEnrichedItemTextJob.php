<?php

namespace App\Jobs;

use App\Models\EnrichedItem;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

/**
 * Supprime le texte extrait brut des sources RSS après 60 jours.
 *
 * Raison : extracted_text contient le contenu complet des articles tiers.
 * Le garder indéfiniment crée un risque copyright inutile — les articles
 * générés ont déjà été produits et sont stockés séparément.
 * On conserve le reste (lead, key_points, seo_keywords) qui sont des
 * dérivés transformés et ne constituent pas une reproduction protégée.
 */
class PruneEnrichedItemTextJob implements ShouldQueue
{
    use Queueable;

    public function handle(): void
    {
        $count = EnrichedItem::query()
            ->whereNotNull('extracted_text')
            ->where('enriched_at', '<', now()->subDays(60))
            ->update(['extracted_text' => null]);

        if ($count > 0) {
            Log::info("PruneEnrichedItemTextJob: {$count} extracted_text(s) supprimé(s) (> 60 jours).");
        }
    }
}
