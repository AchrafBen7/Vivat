<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\SubCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Injecte 4 à 5 sous-catégories pour chaque catégorie existante.
 * À lancer après les seeders de catégories (PipelineSeeder ou VivatNineCategoriesSeeder).
 */
class SubCategoriesSeeder extends Seeder
{
    /** Sous-rubriques par slug de catégorie (4-5 noms par catégorie). */
    private const SUB_BY_CATEGORY = [
        'au-quotidien'   => ['Actualité locale', 'Faits divers', 'Société', 'Éducation', 'Travail'],
        'energie'        => ['Renouvelable', 'Nucléaire', 'Gaz', 'Électricité', 'Transition'],
        'finance'        => ['Marchés', 'Épargne', 'Crédit', 'Fiscalité', 'Crypto'],
        'technologie'    => ['IA', 'Mobile', 'Web', 'Sécurité', 'Cloud'],
        'chez-soi'       => ['Déco', 'Bricolage', 'Jardin', 'Logement', 'Équipement'],
        'mode'           => ['Femme', 'Homme', 'Enfant', 'Tendances', 'Accessoires'],
        'sante'          => ['Médecine', 'Bien-être', 'Nutrition', 'Mental', 'Prévention'],
        'voyage'         => ['Europe', 'Destinations', 'Pratique', 'Culture', 'Aventure'],
        'famille'        => ['Parentalité', 'École', 'Loisirs', 'Ados', 'Seniors'],
        'sport'          => ['Football', 'Tennis', 'Natation', 'Badminton', 'Ski'],
        'justice'        => ['Pénal', 'Civil', 'Administratif', 'International', 'Médiation'],
        'planete'        => ['Climat', 'Biodiversité', 'Déchets', 'Pollution', 'Énergie'],
        'region'         => ['Bruxelles', 'Wallonie', 'Flandre', 'Local', 'Urbanisme'],
        'quotidien'      => ['Politique', 'Économie', 'Culture', 'Sport', 'Société'],
        'environnement'  => ['Climat', 'Pollution', 'Transition', 'Réglementation', 'Biodiversité'],
        'economie'       => ['Marchés', 'Entreprises', 'Emploi', 'Fiscalité', 'Commerce'],
        'alimentation'   => ['Agriculture', 'Nutrition', 'Bio', 'Recettes', 'Durabilité'],
        'societe'        => ['Éducation', 'Culture', 'Justice sociale', 'Droits', 'Solidarité'],
        'transport'      => ['Véhicules', 'Mobilité', 'Rail', 'Aérien', 'Vélo'],
        'habitat'        => ['Construction', 'Rénovation', 'Urbanisme', 'Isolation', 'Chauffage'],
        'biodiversite'   => ['Faune', 'Flore', 'Écosystèmes', 'Protection', 'Océans'],
        'politique'      => ['National', 'Régional', 'Europe', 'Élections', 'Lois'],
        'sciences'       => ['Recherche', 'Études', 'Innovation', 'Santé', 'Climat'],
        'mode-de-vie'    => ['Zéro déchet', 'Consommation', 'DIY', 'Minimalisme', 'Éthique'],
        'international'  => ['Europe', 'Afrique', 'Amériques', 'Asie', 'Géopolitique'],
    ];

    public function run(): void
    {
        $categories = Category::all();
        $defaultSubs = ['Sous-rubrique A', 'Sous-rubrique B', 'Sous-rubrique C', 'Sous-rubrique D', 'Sous-rubrique E'];

        foreach ($categories as $category) {
            $names = self::SUB_BY_CATEGORY[$category->slug] ?? $defaultSubs;
            $count = 0;
            $order = 1;
            foreach (array_slice($names, 0, 5) as $name) {
                $slug = Str::slug($name);
                SubCategory::firstOrCreate(
                    [
                        'category_id' => $category->id,
                        'slug'        => $slug,
                    ],
                    [
                        'name'  => $name,
                        'order' => $order++,
                    ]
                );
                $count++;
            }
            $this->command?->info("Catégorie {$category->name}: {$count} sous-catégories.");
        }

        $this->command?->info('Sous-catégories injectées (4-5 par catégorie).');
    }
}
