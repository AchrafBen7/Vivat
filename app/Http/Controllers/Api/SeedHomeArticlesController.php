<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Database\Seeders\HomeArticlesSeeder;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

class SeedHomeArticlesController extends Controller
{
    /**
     * POST /api/admin/seed-home-articles
     *
     * Crée 10 articles publiés pour tester la home (GET /api/public/home).
     * Réservé admin. À utiliser depuis Postman après login.
     */
    public function __invoke(): JsonResponse
    {
        $seeder = new HomeArticlesSeeder;
        $seeder->run();

        foreach (['fr', 'nl'] as $loc) {
            Cache::forget(config('vivat.home_cache_key_prefix', 'vivat.home.v2') . '.' . $loc);
        }

        return response()->json([
            'message' => '10 articles home créés et publiés (slugs comme dans POSTMAN_10_ARTICLES_HOME_BODIES.md). Appelez GET /api/public/home pour tester.',
        ]);
    }
}
