<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\Category;
use Illuminate\Database\Seeder;

/**
 * Ajoute 12 articles supplémentaires pour afficher toute la section "Dernières actualités"
 * (2 featured + hot news + 7 standards + 2 featured 3e ligne).
 */
class AdditionalArticlesSeeder extends Seeder
{
    public function run(): void
    {
        $categories = Category::query()->get();
        if ($categories->isEmpty()) {
            $this->command?->warn('Aucune catégorie trouvée. Lancez d\'abord VivatNineCategoriesSeeder ou PipelineSeeder.');
            return;
        }

        $categoryBySlug = $categories->keyBy('slug');
        $fallbackCategory = $categories->first();

        $articles = $this->getArticlesData();
        foreach ($articles as $data) {
            if (Article::where('slug', $data['slug'])->exists()) {
                continue;
            }

            $category = $categoryBySlug->get($data['category_slug'] ?? null) ?? $fallbackCategory;

            $article = Article::create([
                'title' => $data['title'],
                'slug' => $data['slug'],
                'excerpt' => $data['excerpt'],
                'content' => $data['content'],
                'meta_title' => $data['meta_title'] ?? $data['title'],
                'meta_description' => $data['meta_description'] ?? $data['excerpt'],
                'keywords' => $data['keywords'] ?? [],
                'category_id' => $category->id,
                'cluster_id' => null,
                'reading_time' => $data['reading_time'],
                'status' => 'review',
                'article_type' => $data['article_type'],
                'cover_image_url' => $data['cover_image_url'] ?? null,
                'quality_score' => 75,
                'published_at' => null,
            ]);

            $article->publish();
            $article->update(['published_at' => $data['published_at'] ?? now()->subDays(rand(0, 14))]);
        }

        $this->command?->info('12 articles supplémentaires créés et publiés.');
    }

    private function getArticlesData(): array
    {
        return [
            [
                'slug' => 'bruxelles-collecte-dechets-verts-quinze-jours',
                'category_slug' => 'environnement',
                'title' => 'Bruxelles : la collecte des déchets verts sera assurée tous les quinze jours',
                'excerpt' => 'La Région confirme le nouveau calendrier à partir du mois prochain. Les citoyens sont invités à consulter les zones.',
                'article_type' => 'standard',
                'reading_time' => 4,
                'cover_image_url' => 'https://picsum.photos/800/600?random=20',
                'keywords' => ['Bruxelles', 'déchets', 'collecte', 'environnement'],
                'published_at' => now()->subDays(2),
                'content' => '<p>La collecte des déchets verts à Bruxelles passera à une fréquence bimensuelle à partir du mois prochain. Les autorités indiquent que cette mesure permettra d\'optimiser les tournées tout en maintenant la qualité du service.</p>',
            ],
            [
                'slug' => 'jack-depp-fils-johnny-vanessa-paradis',
                'category_slug' => 'societe',
                'title' => 'Jack Depp : à 23 ans, le fils de Johnny Depp et Vanessa Paradis sort de l\'ombre',
                'excerpt' => 'Le jeune artiste dévoile son premier projet musical et évoque son parcours entre Los Angeles et Paris.',
                'article_type' => 'hot_news',
                'reading_time' => 4,
                'cover_image_url' => 'https://picsum.photos/1200/800?random=21',
                'keywords' => ['Jack Depp', 'Johnny Depp', 'musique', 'cinéma'],
                'published_at' => now()->subDays(1),
                'content' => '<p>Jack Depp, fils de Johnny Depp et Vanessa Paradis, lance sa carrière musicale. À 23 ans, il présente son premier EP et confie sa volonté de se construire en dehors de l\'ombre de ses parents.</p>',
            ],
            [
                'slug' => 'neerlandais-boris-dillies-we-zien',
                'category_slug' => 'au-quotidien',
                'title' => 'Le néerlandais de Boris Dilliès ? « We zien » ce qu\'on verra !',
                'excerpt' => 'Le présentateur météo belge s\'essaie au néerlandais dans une séquence devenue virale sur les réseaux sociaux.',
                'article_type' => 'standard',
                'reading_time' => 2,
                'cover_image_url' => 'https://picsum.photos/400/300?random=22',
                'keywords' => ['météo', 'RTBF', 'néerlandais', 'Boris Dilliès'],
                'content' => '<p>Boris Dilliès, présentateur météo de la RTBF, a tenté de présenter le bulletin en néerlandais. La séquence amusante a été largement partagée et commentée.</p>',
            ],
            [
                'slug' => 'iad-nouveau-directeur-management-toxique',
                'category_slug' => 'societe',
                'title' => 'Après des accusations de management toxique, l\'IAD désigne un nouveau directeur',
                'excerpt' => 'Le groupe immobilier annonce la nomination d\'un directeur général par intérim en attendant une réorganisation complète.',
                'article_type' => 'standard',
                'reading_time' => 4,
                'cover_image_url' => 'https://picsum.photos/800/600?random=23',
                'keywords' => ['IAD', 'immobilier', 'management', 'direction'],
                'content' => '<p>L\'IAD, acteur majeur de l\'immobilier en Belgique, a nommé un nouveau directeur par intérim suite aux accusations de management toxique qui ont ébranlé l\'entreprise. Une enquête interne est en cours.</p>',
            ],
            [
                'slug' => 'justice-six-noms-epstein-ministère',
                'category_slug' => 'politique',
                'title' => 'Six noms occultés des dossiers Epstein sans explication par le ministère américain de la Justice',
                'excerpt' => 'Les avocats des victimes demandent la levée des redactions dans les documents récemment rendus publics.',
                'article_type' => 'standard',
                'reading_time' => 4,
                'cover_image_url' => 'https://picsum.photos/800/600?random=24',
                'keywords' => ['Epstein', 'Justice', 'États-Unis', 'dossiers'],
                'content' => '<p>Plusieurs pages de documents liés à l\'affaire Epstein ont été rendues publiques. Six noms restent caviardés sans justification détaillée du ministère.</p>',
            ],
            [
                'slug' => 'planete-dechets-verts-collecte-bruxelles',
                'category_slug' => 'environnement',
                'title' => 'Planète : Bruxelles renforce la collecte des déchets organiques',
                'excerpt' => 'Nouvelle fréquence et extension des zones de collecte dès le printemps.',
                'article_type' => 'standard',
                'reading_time' => 3,
                'cover_image_url' => 'https://picsum.photos/800/600?random=25',
                'keywords' => ['planète', 'déchets', 'Bruxelles', 'collecte'],
                'content' => '<p>La Région bruxelloise étend la collecte des déchets organiques à de nouvelles zones et augmente la fréquence dans les quartiers les plus denses.</p>',
            ],
            [
                'slug' => 'sante-autosuffisance-alimentaire-conditions',
                'category_slug' => 'sante',
                'title' => 'L\'autosuffisance alimentaire est possible mais à certaines conditions',
                'excerpt' => 'Une étude détaille les leviers pour tendre vers l\'autonomie alimentaire locale.',
                'article_type' => 'standard',
                'reading_time' => 4,
                'cover_image_url' => null,
                'keywords' => ['santé', 'autosuffisance', 'alimentation', 'agriculture'],
                'content' => '<p>L\'autosuffisance alimentaire locale est atteignable avec des surfaces adéquates, une main-d\'œuvre formée et une réduction du gaspillage.</p>',
            ],
            [
                'slug' => 'technologie-ia-generative-entreprise-2026',
                'category_slug' => 'technologie',
                'title' => 'IA générative en entreprise : tendances 2026',
                'excerpt' => 'Les outils d\'IA s\'intègrent progressivement dans les processus métiers. Tour d\'horizon des usages concrets.',
                'article_type' => 'long_form',
                'reading_time' => 6,
                'cover_image_url' => 'https://picsum.photos/800/600?random=27',
                'keywords' => ['IA', 'entreprise', 'technologie', 'innovation'],
                'content' => '<p>L\'intelligence artificielle générative progresse dans les entreprises. Rédaction, synthèse, support client : les cas d\'usage se multiplient.</p>',
            ],
            [
                'slug' => 'transport-velo-electrique-belgique-croissance',
                'category_slug' => 'transport',
                'title' => 'Vélo électrique : la Belgique parmi les champions européens',
                'excerpt' => 'Les ventes de vélos à assistance électrique ont bondi de 25 % sur l\'année. Les infrastructures suivent-elles ?',
                'article_type' => 'standard',
                'reading_time' => 4,
                'cover_image_url' => 'https://picsum.photos/800/600?random=28',
                'keywords' => ['vélo', 'électrique', 'mobilité', 'Belgique'],
                'content' => '<p>La Belgique se place parmi les pays européens où le vélo électrique connaît la plus forte croissance. Les pistes cyclables et les parkings sécurisés restent un défi.</p>',
            ],
            [
                'slug' => 'habitat-renovation-energetique-aides-2026',
                'category_slug' => 'habitat',
                'title' => 'Rénovation énergétique : les aides en 2026',
                'excerpt' => 'État des lieux des primes et subventions pour isoler et chauffer son logement.',
                'article_type' => 'long_form',
                'reading_time' => 5,
                'cover_image_url' => 'https://picsum.photos/800/600?random=29',
                'keywords' => ['rénovation', 'énergie', 'primes', 'habitat'],
                'content' => '<p>Les dispositifs d\'aide à la rénovation énergétique évoluent chaque année. Voici ce qui change en 2026 pour les particuliers.</p>',
            ],
            [
                'slug' => 'biodiversite-oiseaux-migration-climat',
                'category_slug' => 'biodiversite',
                'title' => 'Biodiversité : les oiseaux migrateurs perturbés par le climat',
                'excerpt' => 'Les changements de température et de précipitations modifient les routes et les dates de migration.',
                'article_type' => 'standard',
                'reading_time' => 4,
                'cover_image_url' => 'https://picsum.photos/800/600?random=30',
                'keywords' => ['biodiversité', 'oiseaux', 'migration', 'climat'],
                'content' => '<p>Les oiseaux migrateurs adaptent leurs parcours et leur calendrier aux modifications du climat. Les scientifiques observent des décalages importants.</p>',
            ],
            [
                'slug' => 'economie-inflation-belgique-fevrier-2026',
                'category_slug' => 'economie',
                'title' => 'Inflation en Belgique : léger repli en février 2026',
                'excerpt' => 'L\'indice des prix à la consommation poursuit sa baisse. Les ménages restent prudents sur les dépenses.',
                'article_type' => 'standard',
                'reading_time' => 3,
                'cover_image_url' => null,
                'keywords' => ['inflation', 'Belgique', 'économie', 'prix'],
                'content' => '<p>L\'inflation en Belgique continue de diminuer. Le coût de l\'énergie et des denrées alimentaires reste toutefois source de préoccupation.</p>',
            ],
        ];
    }
}
