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

        // Pipeline data (categories, sources, feeds, templates)
        $this->call(PipelineSeeder::class);

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
