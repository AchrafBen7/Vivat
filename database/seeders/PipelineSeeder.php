<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\CategoryTemplate;
use App\Models\RssFeed;
use App\Models\Source;
use Illuminate\Database\Seeder;

class PipelineSeeder extends Seeder
{
    public function run(): void
    {
        // ── 1. Catégories (14 catégories thématiques) ──
        $categories = [
            ['name' => 'Environnement', 'slug' => 'environnement', 'description' => 'Écologie, climat, biodiversité', 'color' => '#22c55e'],
            ['name' => 'Santé', 'slug' => 'sante', 'description' => 'Santé publique, médecine, bien-être', 'color' => '#ef4444'],
            ['name' => 'Économie', 'slug' => 'economie', 'description' => 'Économie durable, finance verte', 'color' => '#f59e0b'],
            ['name' => 'Énergie', 'slug' => 'energie', 'description' => 'Énergies renouvelables, transition énergétique', 'color' => '#eab308'],
            ['name' => 'Alimentation', 'slug' => 'alimentation', 'description' => 'Agriculture, alimentation durable, nutrition', 'color' => '#84cc16'],
            ['name' => 'Technologie', 'slug' => 'technologie', 'description' => 'Innovation, tech verte, numérique responsable', 'color' => '#6366f1'],
            ['name' => 'Société', 'slug' => 'societe', 'description' => 'Justice sociale, éducation, culture', 'color' => '#8b5cf6'],
            ['name' => 'Transport', 'slug' => 'transport', 'description' => 'Mobilité durable, véhicules électriques', 'color' => '#06b6d4'],
            ['name' => 'Habitat', 'slug' => 'habitat', 'description' => 'Construction durable, rénovation, urbanisme', 'color' => '#14b8a6'],
            ['name' => 'Biodiversité', 'slug' => 'biodiversite', 'description' => 'Faune, flore, écosystèmes', 'color' => '#10b981'],
            ['name' => 'Politique', 'slug' => 'politique', 'description' => 'Politiques environnementales, réglementation', 'color' => '#f43f5e'],
            ['name' => 'Sciences', 'slug' => 'sciences', 'description' => 'Recherche scientifique, découvertes', 'color' => '#3b82f6'],
            ['name' => 'Mode de vie', 'slug' => 'mode-de-vie', 'description' => 'Consommation responsable, zéro déchet', 'color' => '#ec4899'],
            ['name' => 'International', 'slug' => 'international', 'description' => 'Actualités mondiales, géopolitique verte', 'color' => '#64748b'],
        ];

        $catModels = [];
        foreach ($categories as $cat) {
            $catModels[$cat['slug']] = Category::firstOrCreate(['slug' => $cat['slug']], $cat);
        }

        // ── 2. Sources (médias francophones) ──
        $sources = [
            ['name' => 'Écoconso', 'base_url' => 'https://www.ecoconso.be', 'language' => 'fr', 'is_active' => true],
            ['name' => 'Vert (Le Média)', 'base_url' => 'https://vert.eco', 'language' => 'fr', 'is_active' => true],
            ['name' => 'Futura Sciences', 'base_url' => 'https://www.futura-sciences.com', 'language' => 'fr', 'is_active' => true],
            ['name' => 'Reporterre', 'base_url' => 'https://reporterre.net', 'language' => 'fr', 'is_active' => true],
            ['name' => 'Novethic', 'base_url' => 'https://www.novethic.fr', 'language' => 'fr', 'is_active' => true],
            ['name' => 'Natura Sciences', 'base_url' => 'https://www.natura-sciences.com', 'language' => 'fr', 'is_active' => true],
        ];

        $srcModels = [];
        foreach ($sources as $src) {
            $srcModels[$src['name']] = Source::firstOrCreate(['base_url' => $src['base_url']], $src);
        }

        // ── 3. Flux RSS (liés aux sources et catégories) ──
        $feeds = [
            // Reporterre Environnement
            [
                'source' => 'Reporterre',
                'category' => 'environnement',
                'feed_url' => 'https://reporterre.net/spip.php?page=backend-simple',
                'fetch_interval_minutes' => 60,
            ],
            // Futura Sciences Sciences
            [
                'source' => 'Futura Sciences',
                'category' => 'sciences',
                'feed_url' => 'https://www.futura-sciences.com/rss/environnement/actualites.xml',
                'fetch_interval_minutes' => 60,
            ],
            // Futura Sciences Santé
            [
                'source' => 'Futura Sciences',
                'category' => 'sante',
                'feed_url' => 'https://www.futura-sciences.com/rss/sante/actualites.xml',
                'fetch_interval_minutes' => 60,
            ],
            // Novethic Économie
            [
                'source' => 'Novethic',
                'category' => 'economie',
                'feed_url' => 'https://www.novethic.fr/rss',
                'fetch_interval_minutes' => 120,
            ],
            // Natura Sciences Environnement
            [
                'source' => 'Natura Sciences',
                'category' => 'environnement',
                'feed_url' => 'https://www.natura-sciences.com/feed',
                'fetch_interval_minutes' => 120,
            ],
        ];

        foreach ($feeds as $f) {
            $source = $srcModels[$f['source']] ?? null;
            $category = $catModels[$f['category']] ?? null;

            RssFeed::firstOrCreate(
                ['feed_url' => $f['feed_url']],
                [
                    'source_id' => $source?->id,
                    'category_id' => $category?->id,
                    'feed_url' => $f['feed_url'],
                    'is_active' => true,
                    'fetch_interval_minutes' => $f['fetch_interval_minutes'],
                ]
            );
        }

        // ── 4. Category Templates (config par défaut) ──
        foreach ($catModels as $slug => $cat) {
            CategoryTemplate::firstOrCreate(
                ['category_id' => $cat->id],
                [
                    'category_id' => $cat->id,
                    'tone' => 'professionnel et accessible',
                    'structure' => 'standard',
                    'min_word_count' => 800,
                    'max_word_count' => 1500,
                    'style_notes' => "Article style magazine pour la catégorie {$cat->name}. Ton informatif, engageant, avec des exemples concrets.",
                    'seo_rules' => 'Inclure le mot-clé principal dans le titre H1, le premier paragraphe, et au moins 2 sous-titres H2. Densité de mots-clés : 1-2%.',
                ]
            );
        }

        $this->command->info('Pipeline seeder terminé : ' . count($categories) . ' catégories, ' . count($sources) . ' sources, ' . count($feeds) . ' feeds RSS, ' . count($categories) . ' templates.');
    }
}
