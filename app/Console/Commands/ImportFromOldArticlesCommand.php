<?php

namespace App\Console\Commands;

use App\Models\Article;
use App\Models\Category;
use App\Models\SubCategory;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * Vide la table articles de vivat puis importe 0,5 % des articles de vivat_old.tbl_cont_pg.
 * Mapping : contTitle→title, contDesc→excerpt, contContent→content, contKeywords→keywords,
 * contImgs→cover_image_url, contRef1→category_id (via nom/slug ou tbl_ref), meta_*→meta_*,
 * slug généré, article_type/reading_time/published_at/status renseignés.
 * Seuls les articles dont la catégorie (contRef1) correspond à une catégorie vivat sont importés.
 */
class ImportFromOldArticlesCommand extends Command
{
    protected $signature = 'vivat:import-old-articles
                            {--percent=0.5 : Pourcentage d\'articles à importer (0.5 = 0,5 %)}
                            {--exclude-lang=nl : Langues à exclure (ex: nl pour néerlandais, vide = tout importer)}
                            {--dry-run : Ne pas vider ni insérer, seulement afficher}';

    protected $description = 'Vide articles puis importe 0,5 % des articles de vivat_old.tbl_cont_pg (catégories vivat uniquement). Par défaut exclut le néerlandais (--exclude-lang=nl).';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $percent = (float) $this->option('percent');
        $excludeLangRaw = $this->option('exclude-lang');
        $excludeLangs = $excludeLangRaw === '' || $excludeLangRaw === null
            ? []
            : array_map('strtolower', array_filter(array_map('trim', explode(',', (string) $excludeLangRaw))));
        if ($percent <= 0 || $percent > 100) {
            $this->error('Le pourcentage doit être entre 0 et 100.');
            return self::FAILURE;
        }

        $oldConnection = 'vivat_old';
        $oldTable = 'tbl_cont_pg';

        if (! config('database.connections.'.$oldConnection)) {
            $this->error("Connexion « {$oldConnection} » introuvable. Vérifiez DB_OLD_* dans .env.");
            return self::FAILURE;
        }

        try {
            DB::connection($oldConnection)->getPdo();
        } catch (\Throwable $e) {
            $this->error('Impossible de se connecter à la base vivat_old : '.$e->getMessage());
            return self::FAILURE;
        }

        $this->info('Chargement des catégories et sous-catégories vivat…');
        // Format vivat_old : contRef1 = "categorie/sous-categorie/" → matching insensible à la casse
        $categoriesBySlugLower = [];
        $categoriesByNameLower = [];
        foreach (Category::all() as $c) {
            $categoriesBySlugLower[strtolower($c->slug)] = $c->id;
            $categoriesByNameLower[strtolower($c->name)] = $c->id;
        }
        $subByCategoryAndKey = []; // [category_id => [ 'slug_lower' => sub_id, 'name_lower' => sub_id ]]
        foreach (SubCategory::with('category')->get() as $s) {
            $subByCategoryAndKey[$s->category_id][strtolower($s->slug)] = $s->id;
            $subByCategoryAndKey[$s->category_id][strtolower($s->name)] = $s->id;
        }

        $this->info("Récupération des lignes de {$oldTable}…");
        $allRows = DB::connection($oldConnection)->table($oldTable)->get();
        $totalOld = $allRows->count();
        if ($totalOld === 0) {
            $this->warn("Aucune ligne dans {$oldTable}.");
            return self::SUCCESS;
        }

        $resolveCategoryAndSub = function ($contRef1) use ($categoriesBySlugLower, $categoriesByNameLower, $subByCategoryAndKey) {
            if ($contRef1 === null || $contRef1 === '') {
                return [null, null];
            }
            $s = trim((string) $contRef1, " \t\n\r/");
            $parts = array_values(array_filter(explode('/', $s), fn ($p) => trim((string) $p) !== ''));
            if (count($parts) === 0) {
                return [null, null];
            }
            $catKey = strtolower(trim($parts[0]));
            $categoryId = $categoriesBySlugLower[$catKey] ?? $categoriesByNameLower[$catKey] ?? null;
            if ($categoryId === null) {
                return [null, null];
            }
            $subCategoryId = null;
            if (isset($parts[1])) {
                $subKey = strtolower(trim($parts[1]));
                $subCategoryId = $subByCategoryAndKey[$categoryId][$subKey] ?? null;
            }
            return [$categoryId, $subCategoryId];
        };

        $candidates = [];
        foreach ($allRows as $row) {
            $contRef1 = $row->contRef1 ?? $row->contRef ?? null;
            [$catId, $subId] = $resolveCategoryAndSub($contRef1);
            if ($catId === null) {
                continue;
            }
            $rowLang = isset($row->contLang) ? strtolower(trim((string) $row->contLang)) : '';
            if ($rowLang !== '' && in_array($rowLang, $excludeLangs, true)) {
                continue;
            }
            $candidates[] = (object) [
                'row' => $row,
                'category_id' => $catId,
                'sub_category_id' => $subId,
            ];
        }

        $takeCount = max(1, (int) ceil(count($candidates) * ($percent / 100)));
        $toImport = collect($candidates)->random(min($takeCount, count($candidates)));

        $excludeInfo = $excludeLangs !== [] ? ' (langues exclues : '.implode(', ', $excludeLangs).')' : '';
        $this->info("Sur {$totalOld} lignes, ".count($candidates).' ont une catégorie vivat'.$excludeInfo.'. Import de '.$toImport->count().' ('.$percent.' %).');

        if ($dryRun) {
            $this->info('[DRY-RUN] Aucune modification.');
            foreach ($toImport->take(5) as $c) {
                $this->line('  '.($c->row->contTitle ?? 'sans titre').' → catégorie id '.$c->category_id);
            }
            if ($toImport->count() > 5) {
                $this->line('  … et '.($toImport->count() 5).' autres.');
            }
            return self::SUCCESS;
        }

        $this->info('Vidage des tables dépendantes puis articles…');
        if (Schema::hasTable('article_sources')) {
            DB::table('article_sources')->delete();
        }
        if (Schema::hasTable('reading_histories')) {
            DB::table('reading_histories')->delete();
        }
        Article::query()->delete();

        $usedSlugs = [];
        $bar = $this->output->createProgressBar($toImport->count());
        $bar->start();

        foreach ($toImport as $c) {
            $row = $c->row;
            $title = $row->contTitle ?? 'Sans titre';
            $baseSlug = Str::slug($title) ?: 'article-'.($row->contID ?? Str::random(6));
            $slug = $baseSlug;
            $suffix = 0;
            while (in_array($slug, $usedSlugs, true) || Article::where('slug', $slug)->exists()) {
                $suffix++;
                $slug = $baseSlug.'-'.$suffix;
            }
            $usedSlugs[] = $slug;

            $content = $row->contContent ?? '';
            $wordCount = str_word_count(strip_tags($content)) ?: 1;
            $readingTime = (int) max(1, min(60, round($wordCount / 200)));
            if (! empty($row->contPgs) && is_numeric($row->contPgs)) {
                $readingTime = (int) max(1, min(60, (int) $row->contPgs));
            }

            $publishedAt = null;
            try {
                if (! empty($row->contDate)) {
                    $publishedAt = \Carbon\Carbon::parse($row->contDate);
                } elseif (! empty($row->contPublishDate) && is_numeric($row->contPublishDate)) {
                    $ts = (int) $row->contPublishDate;
                    if ($ts > 0 && $ts < 2147483647) {
                        $publishedAt = \Carbon\Carbon::createFromTimestamp($ts);
                    }
                } elseif (! empty($row->creation)) {
                    $publishedAt = \Carbon\Carbon::parse($row->creation);
                }
            } catch (\Throwable $e) {
                $publishedAt = null;
            }
            if (! $publishedAt || $publishedAt->isFuture() || $publishedAt->year < 1970) {
                $publishedAt = now();
            }

            $keywords = $row->contKeywords ?? null;
            if (is_string($keywords)) {
                $keywords = array_filter(array_map('trim', explode(',', $keywords)));
                $keywords = empty($keywords) ? null : $keywords;
            }

            $articleLang = isset($row->contLang) ? strtolower(trim((string) $row->contLang)) : 'fr';
            if (! in_array($articleLang, ['fr', 'nl'], true)) {
                $articleLang = 'fr';
            }

            Article::create([
                'title' => $title,
                'slug' => $slug,
                'excerpt' => $row->contDesc ?? null,
                'content' => $content ?: '<p></p>',
                'meta_title' => $row->meta_title ?? null,
                'meta_description' => $row->meta_desc ?? null,
                'keywords' => $keywords,
                'category_id' => $c->category_id,
                'language' => $articleLang,
                'sub_category_id' => $c->sub_category_id,
                'cluster_id' => null,
                'reading_time' => $readingTime,
                'status' => 'published',
                'article_type' => 'standard',
                'cover_image_url' => $row->contImgs ?? null,
                'cover_video_url' => null,
                'quality_score' => 75,
                'published_at' => $publishedAt,
            ]);
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info('Import terminé : '.$toImport->count().' articles insérés.');

        return self::SUCCESS;
    }
}
