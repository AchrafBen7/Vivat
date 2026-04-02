<?php

namespace App\Console\Commands;

use App\Models\Category;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class ResetCategoriesCommand extends Command
{
    /**
     * @var string
     */
    protected $signature = 'vivat:reset-categories
                            {--force : Ne pas demander de confirmation}';

    protected $description = 'Supprime toutes les catégories (pour repartir sur les 9 catégories fixes). Les articles/feeds gardent category_id à null.';

    public function handle(): int
    {
        $count = Category::query()->count();
        if ($count === 0) {
            $this->info('Aucune catégorie à supprimer.');

            return self::SUCCESS;
        }

        if (! $this->option('force') && ! $this->confirm(sprintf('Supprimer les %d catégorie(s) ? Les articles et flux auront category_id = null.', $count))) {
            return self::SUCCESS;
        }

        Category::query()->delete();
        Cache::forget('vivat.categories.index');
        foreach (['fr', 'nl'] as $loc) {
            Cache::forget(config('vivat.home_cache_key_prefix', 'vivat.home.v2') . '.' . $loc);
        }

        $this->info(sprintf('%d catégorie(s) supprimée(s). Tu peux recréer les 9 catégories via POST /api/categories (voir docs/POSTMAN_9_CATEGORIES_BODIES.md).', $count));

        return self::SUCCESS;
    }
}
