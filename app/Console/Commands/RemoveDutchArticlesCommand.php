<?php

namespace App\Console\Commands;

use App\Models\Article;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Supprime les articles en néerlandais (language = 'nl') de la base.
 * À exécuter après avoir ajouté la colonne language (migration) et éventuellement
 * après un import qui a enregistré la langue.
 */
class RemoveDutchArticlesCommand extends Command
{
    protected $signature = 'vivat:remove-dutch-articles
                            {--dry-run : Afficher le nombre sans supprimer}';

    protected $description = 'Supprime les articles en néerlandais (language = nl) de la base';

    public function handle(): int
    {
        if (! Schema::hasColumn('articles', 'language')) {
            $this->error('La table articles n\'a pas de colonne language. Exécutez d\'abord la migration qui l\'ajoute.');
            return self::FAILURE;
        }

        $count = Article::where('language', 'nl')->count();
        if ($count === 0) {
            $this->info('Aucun article en néerlandais en base.');
            return self::SUCCESS;
        }

        if ($this->option('dry-run')) {
            $this->info("[DRY-RUN] {$count} article(s) en néerlandais seraient supprimés.");
            return self::SUCCESS;
        }

        $this->info("Suppression de {$count} article(s) en néerlandais…");
        $ids = Article::where('language', 'nl')->pluck('id');
        DB::transaction(function () use ($ids) {
            if (Schema::hasTable('article_sources')) {
                DB::table('article_sources')->whereIn('article_id', $ids)->delete();
            }
            if (Schema::hasTable('reading_histories')) {
                DB::table('reading_histories')->whereIn('article_id', $ids)->delete();
            }
            Article::whereIn('id', $ids)->delete();
        });
        $this->info('Articles néerlandais supprimés.');

        return self::SUCCESS;
    }
}
