<?php

namespace App\Console\Commands;

use App\Models\Category;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

/**
 * Vide le cache de la page d'accueil (home) et des hubs catégories pour les deux langues.
 * À lancer après un import, après vivat:clear-cover-images, ou si des articles obsolètes / sans photo s'affichent.
 */
class ClearHomeCacheCommand extends Command
{
    protected $signature = 'vivat:clear-home-cache';

    protected $description = 'Vide le cache home et hubs (fr et nl) pour forcer un rechargement avec les données à jour';

    public function handle(): int
    {
        $locales = ['fr', 'nl'];
        foreach ($locales as $locale) {
            Cache::forget('vivat.home.' . $locale);
        }
        $this->info('Cache home (fr et nl) vidé.');

        try {
            $slugs = Category::query()->pluck('slug');
            foreach ($slugs as $slug) {
                foreach ($locales as $locale) {
                    Cache::forget('vivat.hub.' . $slug . '.' . $locale);
                }
            }
            $this->info('Cache hubs catégories vidé.');
        } catch (\Throwable $e) {
            $this->comment('Hubs : impossible de vider (base non dispo ou table categories absente).');
        }

        return self::SUCCESS;
    }
}
