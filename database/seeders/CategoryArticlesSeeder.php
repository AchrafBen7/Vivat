<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\Category;
use Illuminate\Database\Seeder;

/**
 * Crée 5 articles publiés par catégorie pour une bonne base visuelle.
 * Répartis sur différentes sous-catégories et types (hot_news, standard, long_form).
 * Les dates de publication sont récentes pour alimenter la home et les pages catégories.
 */
class CategoryArticlesSeeder extends Seeder
{
    private const ARTICLES_PER_CATEGORY = 5;

    /** Types d'articles à répartir : 1 hot_news, 2 standard, 2 long_form par catégorie. */
    private const TYPES_PER_CATEGORY = ['hot_news', 'standard', 'standard', 'long_form', 'long_form'];

    /** Titres et extraits génériques par index (réutilisables avec variante catégorie). */
    private const TITLE_TEMPLATES = [
        'Les dernières tendances dans le domaine : ce qu\'il faut retenir',
        'Décryptage : les enjeux qui marquent l\'actualité cette semaine',
        'Enquête : comment les acteurs du secteur s\'adaptent aux changements',
        'Point de vue : analyse et perspectives pour les mois à venir',
        'Reportage sur le terrain : témoignages et chiffres clés',
    ];

    private const EXCERPT_TEMPLATES = [
        'Une synthèse des évolutions récentes et des réactions des professionnels.',
        'Les principaux enseignements à retenir pour mieux comprendre la situation.',
        'Sur place, les acteurs livrent leur lecture des événements en cours.',
        'Les experts dressent un bilan et formulent des recommandations concrètes.',
        'Retour sur les faits marquants et les décisions attendues prochainement.',
    ];

    public function run(): void
    {
        $categories = Category::with(['subCategories' => fn ($q) => $q->orderBy('order')])->get();
        if ($categories->isEmpty()) {
            $this->command?->warn('Aucune catégorie. Lancez d\'abord PipelineSeeder ou VivatNineCategoriesSeeder puis SubCategoriesSeeder.');
            return;
        }

        $created = 0;

        foreach ($categories as $category) {
            $subs = $category->subCategories;
            for ($i = 0; $i < self::ARTICLES_PER_CATEGORY; $i++) {
                $slug = 'vivat-' . $category->slug . '-' . ($i + 1) . '-' . substr(md5($category->id . $i), 0, 6);
                Article::where('slug', $slug)->delete();

                $subCategoryId = $subs->isNotEmpty() ? $subs[$i % $subs->count()]->id : null;
                $articleType = self::TYPES_PER_CATEGORY[$i];
                $title = self::TITLE_TEMPLATES[$i];
                $excerpt = self::EXCERPT_TEMPLATES[$i];
                $content = '<p>' . $excerpt . '</p><p>Article de test pour la catégorie ' . htmlspecialchars($category->name) . ' — type ' . $articleType . '.</p>';

                // Environ 3 articles sur 5 avec image pour varier l'affichage (highlight, featured)
                $withImage = in_array($i, [0, 1, 3], true);
                $coverImageUrl = $withImage ? 'https://picsum.photos/seed/' . $slug . '/800/600' : null;

                $article = Article::create([
                    'title' => $title,
                    'slug' => $slug,
                    'excerpt' => $excerpt,
                    'content' => $content,
                    'meta_title' => $title,
                    'meta_description' => $excerpt,
                    'keywords' => [],
                    'category_id' => $category->id,
                    'sub_category_id' => $subCategoryId,
                    'cluster_id' => null,
                    'reading_time' => rand(3, 8),
                    'status' => 'review',
                    'article_type' => $articleType,
                    'cover_image_url' => $coverImageUrl,
                    'quality_score' => 75,
                    'published_at' => null,
                ]);

                $article->publish();
                // Dates très récentes (dernières 48h) pour alimenter la home "plus récents"
                $article->update(['published_at' => now()->subHours(rand(0, 48))]);
                $created++;
            }
        }

        $this->command?->info("{$created} articles créés (5 par catégorie, sous-catégories et types variés).");
    }
}
