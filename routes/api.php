<?php

use App\Http\Controllers\Api\ArticleController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\CategoryTemplateController;
use App\Http\Controllers\Api\ClusterController;
use App\Http\Controllers\Api\HomeController;
use App\Http\Controllers\Api\NewsletterController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\PipelineController;
use App\Http\Controllers\Api\PreferenceController;
use App\Http\Controllers\Api\ReadingHistoryController;
use App\Http\Controllers\Api\RssFeedController;
use App\Http\Controllers\Api\RssItemController;
use App\Http\Controllers\Api\SeedHomeArticlesController;
use App\Http\Controllers\Api\SourceController;
use App\Http\Controllers\Api\StatsController;
use App\Http\Controllers\Api\StripeWebhookController;
use App\Http\Controllers\Api\SubCategoryController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes — Vivat
|--------------------------------------------------------------------------
| Préfixe automatique : /api
| Auth : Laravel Sanctum (Bearer token)
| Rôles : admin, contributor (via spatie/permission)
*/

// ╔══════════════════════════════════════════════════════════════════════╗
// ║  AUTH (public)                                                      ║
// ╚══════════════════════════════════════════════════════════════════════╝
Route::prefix('auth')->group(function () {
    Route::post('register', [AuthController::class, 'register'])->middleware(['bot.protect:api', 'throttle:api-auth-register'])->name('auth.register');
    Route::post('login', [AuthController::class, 'login'])->middleware(['bot.protect:api', 'throttle:api-auth-login'])->name('auth.login');

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('logout', [AuthController::class, 'logout'])->name('auth.logout');
        Route::get('me', [AuthController::class, 'me'])->name('auth.me');
        Route::put('profile', [AuthController::class, 'updateProfile'])->name('auth.profile');
    });
});

// ╔══════════════════════════════════════════════════════════════════════╗
// ║  PUBLIC — Lecture seule, aucun auth requis                          ║
// ╚══════════════════════════════════════════════════════════════════════╝
Route::prefix('public')->group(function () {
    // Home (top_news, featured, latest, categories, writer_cta)
    Route::get('home', [HomeController::class, 'index'])->name('public.home');

    // Articles publiés
    Route::get('articles', [ArticleController::class, 'published'])->name('public.articles.index');
    Route::get('articles/{article:slug}', [ArticleController::class, 'showBySlug'])->name('public.articles.show');

    // Catégories + page hub
    Route::get('categories', [CategoryController::class, 'index'])->name('public.categories.index');
    Route::get('categories/{category:slug}', [CategoryController::class, 'show'])->name('public.categories.show');
    Route::get('categories/{category:slug}/hub', [CategoryController::class, 'hub'])->name('public.categories.hub');

    // Recherche
    Route::get('search', [ArticleController::class, 'search'])->name('public.search');

    // Préférences (cookie-based ou auth)
    Route::get('preferences', [PreferenceController::class, 'show'])->name('public.preferences.show');
    Route::post('preferences', [PreferenceController::class, 'store'])->name('public.preferences.store');

    // Recommandations
    Route::get('recommendations', [ArticleController::class, 'recommendations'])->name('public.recommendations');

    // Progression de lecture
    Route::get('reading-progress', [ReadingHistoryController::class, 'index'])->name('public.reading-progress.index');
    Route::post('reading-progress', [ReadingHistoryController::class, 'store'])->name('public.reading-progress.store');
});

// ╔══════════════════════════════════════════════════════════════════════╗
// ║  NEWSLETTER (public)                                                ║
// ╚══════════════════════════════════════════════════════════════════════╝
Route::prefix('newsletter')->group(function () {
    Route::post('subscribe', [NewsletterController::class, 'subscribe'])->middleware(['bot.protect:api', 'throttle:api-newsletter-subscribe'])->name('api.newsletter.subscribe');
    Route::post('unsubscribe', [NewsletterController::class, 'unsubscribe'])->middleware(['bot.protect:api', 'throttle:api-newsletter-actions'])->name('api.newsletter.unsubscribe');
    Route::get('confirm', [NewsletterController::class, 'confirm'])->middleware(['bot.protect:api', 'throttle:api-newsletter-actions'])->name('api.newsletter.confirm');
});

// ╔══════════════════════════════════════════════════════════════════════╗
// ║  STRIPE WEBHOOK (public)                                            ║
// ╚══════════════════════════════════════════════════════════════════════╝
Route::post('stripe/webhook', StripeWebhookController::class)->name('stripe.webhook');

// ╔══════════════════════════════════════════════════════════════════════╗
// ║  CONTRIBUTOR — auth:sanctum + role:contributor|admin                 ║
// ╚══════════════════════════════════════════════════════════════════════╝
Route::middleware(['auth:sanctum', 'role:contributor|admin'])->prefix('contributor')->group(function () {
    // Soumissions
    Route::get('submissions', [App\Http\Controllers\Api\ContributorSubmissionController::class, 'index'])->name('contributor.submissions.index');
    Route::post('submissions', [App\Http\Controllers\Api\ContributorSubmissionController::class, 'store'])->name('contributor.submissions.store');
    Route::get('submissions/{submission}', [App\Http\Controllers\Api\ContributorSubmissionController::class, 'show'])->name('contributor.submissions.show');
    Route::put('submissions/{submission}', [App\Http\Controllers\Api\ContributorSubmissionController::class, 'update'])->name('contributor.submissions.update');
    Route::delete('submissions/{submission}', [App\Http\Controllers\Api\ContributorSubmissionController::class, 'destroy'])->name('contributor.submissions.destroy');

    // Paiements (contributeur)
    Route::post('payments/create-intent', [PaymentController::class, 'createIntent'])->name('contributor.payments.create-intent');
    Route::post('payments/confirm', [PaymentController::class, 'confirm'])->name('contributor.payments.confirm');
    Route::get('payments', [PaymentController::class, 'myPayments'])->name('contributor.payments.index');
});

// ╔══════════════════════════════════════════════════════════════════════╗
// ║  ADMIN — auth:sanctum + role:admin                                  ║
// ╚══════════════════════════════════════════════════════════════════════╝
Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
    // Sources
    Route::apiResource('sources', SourceController::class);

    // Catégories (full CRUD pour admin)
    Route::apiResource('categories', CategoryController::class);
    Route::get('categories/{category}/sub-categories', [SubCategoryController::class, 'index'])->name('categories.sub-categories.index');
    Route::post('categories/{category}/sub-categories', [SubCategoryController::class, 'store'])->name('categories.sub-categories.store');
    Route::put('sub-categories/{subCategory}', [SubCategoryController::class, 'update'])->name('sub-categories.update');
    Route::delete('sub-categories/{subCategory}', [SubCategoryController::class, 'destroy'])->name('sub-categories.destroy');

    // Category Templates
    Route::apiResource('category-templates', CategoryTemplateController::class);

    // RSS Feeds
    Route::apiResource('rss-feeds', RssFeedController::class);

    // RSS Items (lecture seule)
    Route::apiResource('rss-items', RssItemController::class)->only(['index', 'show']);

    // Clusters
    Route::apiResource('clusters', ClusterController::class);

    // Articles (full CRUD + generation + publication)
    Route::prefix('articles')->group(function () {
        Route::post('generate', [ArticleController::class, 'generate'])->middleware('throttle:admin-pipeline-actions')->name('articles.generate');
        Route::post('generate-async', [ArticleController::class, 'generateAsync'])->middleware('throttle:admin-pipeline-actions')->name('articles.generate-async');
        Route::post('{article}/publish', [ArticleController::class, 'publish'])->middleware('throttle:admin-moderation-actions')->name('articles.publish');
    });
    Route::apiResource('articles', ArticleController::class);

    // Pipeline (déclenchement et monitoring)
    Route::prefix('pipeline')->group(function () {
        Route::post('fetch-rss', [PipelineController::class, 'fetchRss'])->middleware('throttle:admin-pipeline-actions')->name('pipeline.fetch-rss');
        Route::post('enrich', [PipelineController::class, 'enrich'])->middleware('throttle:admin-pipeline-actions')->name('pipeline.enrich');
        Route::get('select-items', [PipelineController::class, 'selectItems'])->middleware('throttle:admin-pipeline-actions')->name('pipeline.select-items');
        Route::get('status', [PipelineController::class, 'status'])->name('pipeline.status');
        Route::get('export-trends-csv', [PipelineController::class, 'exportTrendsCsv'])->middleware('throttle:admin-pipeline-actions')->name('pipeline.export-trends-csv');
        Route::post('analyze-trends', [PipelineController::class, 'analyzeTrends'])->middleware('throttle:admin-pipeline-actions')->name('pipeline.analyze-trends');
    });

    // Stats dashboard
    Route::get('stats', [StatsController::class, 'index'])->name('stats.index');

    // Seed 10 articles pour tester la home (Postman / dev)
    Route::post('seed-home-articles', SeedHomeArticlesController::class)->middleware('throttle:admin-pipeline-actions')->name('admin.seed-home-articles');

    // Moderation des soumissions
    Route::prefix('submissions')->group(function () {
        Route::get('/', [App\Http\Controllers\Api\AdminSubmissionController::class, 'index'])->name('admin.submissions.index');
        Route::get('{submission}', [App\Http\Controllers\Api\AdminSubmissionController::class, 'show'])->name('admin.submissions.show');
        Route::post('{submission}/approve', [App\Http\Controllers\Api\AdminSubmissionController::class, 'approve'])->middleware('throttle:admin-moderation-actions')->name('admin.submissions.approve');
        Route::post('{submission}/reject', [App\Http\Controllers\Api\AdminSubmissionController::class, 'reject'])->middleware('throttle:admin-moderation-actions')->name('admin.submissions.reject');
    });

    // Newsletter subscribers (admin)
    Route::get('newsletter/subscribers', [NewsletterController::class, 'subscribers'])->name('admin.newsletter.subscribers');

    // Remboursement (admin)
    Route::post('payments/{payment}/refund', [PaymentController::class, 'refund'])->middleware('throttle:admin-financial-actions')->name('admin.payments.refund');
});
