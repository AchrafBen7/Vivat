<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

/**
 * Vide le cache de la page d'accueil (home) pour les deux langues.
 * À lancer après un import ou si des articles nl s'affichent alors que le système est en fr,
 * ou en cas de doublons dans "Dernières actualités".
 */
class ClearHomeCacheCommand extends Command
{
    protected $signature = 'vivat:clear-home-cache';

    protected $description = 'Vide le cache de la page d\'accueil (fr et nl) pour forcer un rechargement avec les bons articles et sans doublons';

    public function handle(): int
    {
        foreach (['fr', 'nl'] as $locale) {
            Cache::forget('vivat.home.' . $locale);
        }
        $this->info('Cache home (fr et nl) vidé.');

        return self::SUCCESS;
    }
}
