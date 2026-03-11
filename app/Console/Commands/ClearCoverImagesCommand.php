<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Vide toutes les URLs de couverture (cover_image_url) de la table articles.
 * Le site affichera alors automatiquement des photos Unsplash par catégorie.
 *
 * - En local avec Docker : docker compose exec app php artisan vivat:clear-cover-images
 * - Sans Docker (MySQL sur la machine) : php artisan vivat:clear-cover-images
 */
class ClearCoverImagesCommand extends Command
{
    protected $signature = 'vivat:clear-cover-images';

    protected $description = 'Met à NULL toutes les cover_image_url des articles (remplacées par Unsplash à l\'affichage)';

    public function handle(): int
    {
        $count = DB::table('articles')->whereNotNull('cover_image_url')->update(['cover_image_url' => null]);

        $this->info("Cover image vidée pour {$count} article(s). Les images Unsplash s'afficheront à la place.");

        return self::SUCCESS;
    }
}
