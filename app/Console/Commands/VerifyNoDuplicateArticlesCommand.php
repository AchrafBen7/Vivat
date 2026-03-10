<?php

namespace App\Console\Commands;

use App\Models\Category;
use App\Services\PublicPageDataService;
use Illuminate\Console\Command;

/**
 * Vérifie qu'aucun article (par id) n'apparaît deux fois sur la home ni sur les hubs catégorie.
 * - Home : aucun id ne doit être à la fois dans highlight et dans latest ; aucun doublon dans highlight ; aucun doublon dans latest.
 * - Hub : aucun doublon dans la liste d'articles.
 */
class VerifyNoDuplicateArticlesCommand extends Command
{
    protected $signature = 'vivat:verify-no-duplicate-articles
                            {--home-only : Vérifier uniquement la home}
                            {--hub-only= : Slug d\'une catégorie pour vérifier uniquement ce hub}';

    protected $description = 'Vérifie qu\'aucun article n\'apparaît en double sur la home (highlight + dernières actualités) ni sur les hubs';

    public function handle(PublicPageDataService $pageData): int
    {
        $homeOnly = $this->option('home-only');
        $hubSlug = $this->option('hub-only');

        $failed = false;

        if (! $hubSlug) {
            $this->info('Vérification HOME (fr)…');
            if ($this->verifyHome($pageData, 'fr')) {
                $this->info('  → OK : aucun doublon sur la home.');
            } else {
                $failed = true;
            }
        }

        if (! $homeOnly && ! $hubSlug) {
            $categories = Category::orderBy('name')->pluck('slug');
            foreach ($categories as $slug) {
                $this->info("Vérification HUB « {$slug} » (fr)…");
                if ($this->verifyHub($pageData, $slug, 'fr')) {
                    $this->info("  → OK : aucun doublon sur le hub {$slug}.");
                } else {
                    $failed = true;
                }
            }
        }

        if ($hubSlug) {
            $this->info("Vérification HUB « {$hubSlug} » (fr)…");
            if ($this->verifyHub($pageData, $hubSlug, 'fr')) {
                $this->info("  → OK : aucun doublon sur le hub {$hubSlug}.");
            } else {
                $failed = true;
            }
        }

        if ($failed) {
            $this->error('Au moins une vérification a échoué.');
            return self::FAILURE;
        }

        $this->info('Toutes les vérifications sont passées.');
        return self::SUCCESS;
    }

    private function verifyHome(PublicPageDataService $pageData, string $locale): bool
    {
        $data = $pageData->getHomeData($locale);

        $highlight = $data['highlight'] ?? [];
        $latest = $data['latest'] ?? [];

        $highlightIds = [];
        foreach ($highlight as $item) {
            if ($item !== null && isset($item['id'])) {
                $highlightIds[] = $item['id'];
            }
        }

        $latestIds = [];
        foreach ($latest as $item) {
            $id = $item['id'] ?? null;
            if ($id !== null) {
                $latestIds[] = $id;
            }
        }

        $ok = true;

        $highlightCounts = array_count_values($highlightIds);
        foreach ($highlightCounts as $id => $count) {
            if ($count > 1) {
                $this->error("  Home HIGHLIGHT : l'article id [{$id}] apparaît {$count} fois.");
                $ok = false;
            }
        }

        $latestCounts = array_count_values($latestIds);
        foreach ($latestCounts as $id => $count) {
            if ($count > 1) {
                $this->error("  Home LATEST (Dernières actualités) : l'article id [{$id}] apparaît {$count} fois.");
                $ok = false;
            }
        }

        $overlap = array_intersect($highlightIds, $latestIds);
        if (count($overlap) > 0) {
            foreach ($overlap as $id) {
                $this->error("  Home : l'article id [{$id}] est à la fois dans HIGHLIGHT et dans LATEST.");
            }
            $ok = false;
        }

        return $ok;
    }

    private function verifyHub(PublicPageDataService $pageData, string $categorySlug, string $locale): bool
    {
        try {
            $data = $pageData->getCategoryHubData($categorySlug, null, $locale);
        } catch (\Throwable $e) {
            $this->error("  Impossible de charger le hub : " . $e->getMessage());
            return false;
        }

        $articles = $data['articles'] ?? [];
        $ids = [];
        foreach ($articles as $item) {
            $id = $item['id'] ?? null;
            if ($id !== null) {
                $ids[] = $id;
            }
        }

        $counts = array_count_values($ids);
        $ok = true;
        foreach ($counts as $id => $count) {
            if ($count > 1) {
                $this->error("  Hub « {$categorySlug} » : l'article id [{$id}] apparaît {$count} fois.");
                $ok = false;
            }
        }

        return $ok;
    }
}
