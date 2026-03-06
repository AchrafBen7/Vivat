<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // ── Permissions ──
        $permissions = [
            // Articles (generated)
            'articles.view',
            'articles.create',
            'articles.update',
            'articles.delete',
            'articles.publish',
            'articles.generate',

            // Pipeline
            'pipeline.fetch-rss',
            'pipeline.enrich',
            'pipeline.select',
            'pipeline.status',

            // Sources / Feeds
            'sources.manage',
            'feeds.manage',

            // Categories / Templates
            'categories.manage',
            'templates.manage',
            'clusters.manage',

            // Users
            'users.manage',

            // Submissions (contributeur)
            'submissions.create',
            'submissions.view-own',
            'submissions.moderate',

            // Newsletter
            'newsletter.manage',

            // Payments
            'payments.manage',
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
        }

        // ── Roles ──
        $admin = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $admin->syncPermissions($permissions); // admin a tout

        $contributor = Role::firstOrCreate(['name' => 'contributor', 'guard_name' => 'web']);
        $contributor->syncPermissions([
            'articles.view',
            'submissions.create',
            'submissions.view-own',
        ]);

        $this->command->info('Roles & Permissions seeded: admin (all), contributor (submissions).');
    }
}
