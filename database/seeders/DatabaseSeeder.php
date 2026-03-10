<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Roles & Permissions must be seeded FIRST
        $this->call(RolesAndPermissionsSeeder::class);

        // 9 catégories Vivat (au-quotidien, energie, finance, technologie, chez-soi, mode, sante, voyage, famille)
        $this->call(VivatNineCategoriesSeeder::class);

        // 4-5 sous-catégories par catégorie (filtres page catégorie)
        $this->call(SubCategoriesSeeder::class);

        // 5 articles par catégorie (sous-catégories et types variés, base visuelle)
        $this->call(CategoryArticlesSeeder::class);

        // Admin user
        $admin = User::firstOrCreate(
            ['email' => 'admin@vivat.be'],
            [
                'name'     => 'Admin Vivat',
                'password' => bcrypt('password'),
                'language' => 'fr',
            ]
        );
        $admin->assignRole('admin');

        // Contributor user (for testing)
        $contributor = User::firstOrCreate(
            ['email' => 'contributeur@vivat.be'],
            [
                'name'     => 'Contributeur Test',
                'password' => bcrypt('password'),
                'language' => 'fr',
            ]
        );
        $contributor->assignRole('contributor');
    }
}
