<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class VivatNineCategoriesSeeder extends Seeder
{
    /**
     * Crée exactement les 9 catégories prévues pour la home Vivat.
     * À utiliser à la place (ou après nettoyage) des 14 catégories du PipelineSeeder
     * si tu veux uniquement ces 9 rubriques.
     *
     * Connexion phpMyAdmin : http://localhost:8080 — user: vivat, password: vivat_secret
     */
    public function run(): void
    {
        $categories = [
            ['name' => 'Au quotidien', 'slug' => 'au-quotidien', 'description' => 'Rubrique catch-all du quotidien', 'image_url' => 'https://picsum.photos/400/300?random=quotidien', 'home_order' => 1],
            ['name' => 'Énergie', 'slug' => 'energie', 'description' => 'Énergies renouvelables, transition énergétique', 'image_url' => 'https://picsum.photos/400/300?random=energie', 'home_order' => 2],
            ['name' => 'Finance', 'slug' => 'finance', 'description' => 'Argent, finance, investissement', 'image_url' => 'https://picsum.photos/400/300?random=finance', 'home_order' => 3],
            ['name' => 'Technologie', 'slug' => 'technologie', 'description' => 'Innovation, tech, numérique', 'image_url' => 'https://picsum.photos/400/300?random=techno', 'home_order' => 4],
            ['name' => 'Chez soi', 'slug' => 'chez-soi', 'description' => 'Maison, déco, DIY, logement', 'image_url' => 'https://picsum.photos/400/300?random=chezsoi', 'home_order' => 5],
            ['name' => 'Mode', 'slug' => 'mode', 'description' => 'Mode, tendances, style', 'image_url' => 'https://picsum.photos/400/300?random=mode', 'home_order' => 6],
            ['name' => 'Santé', 'slug' => 'sante', 'description' => 'Santé publique, médecine, bien-être', 'image_url' => 'https://picsum.photos/400/300?random=sante', 'home_order' => 7],
            ['name' => 'Voyage', 'slug' => 'voyage', 'description' => 'Voyage, découverte, culture', 'image_url' => 'https://picsum.photos/400/300?random=voyage', 'home_order' => 8],
            ['name' => 'Famille', 'slug' => 'famille', 'description' => 'Famille, relations, parentalité', 'image_url' => 'https://picsum.photos/400/300?random=famille', 'home_order' => 9],
        ];

        foreach ($categories as $cat) {
            Category::updateOrCreate(
                ['slug' => $cat['slug']],
                array_merge($cat, ['created_at' => now()])
            );
        }

        $this->command?->info('9 catégories Vivat créées ou déjà présentes (slugs : au-quotidien, energie, finance, technologie, chez-soi, mode, sante, voyage, famille).');
    }
}
