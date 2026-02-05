<?php

use App\Http\Controllers\Api\ArticleController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\RssFeedController;
use App\Http\Controllers\Api\RssItemController;
use App\Http\Controllers\Api\SourceController;
use App\Http\Controllers\Api\StatsController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes — Vivat Pipeline
|--------------------------------------------------------------------------
| Préfixe : /api (automatique). Testables dans Postman.
| Auth optionnelle : ajouter middleware('auth:sanctum') sur les routes à protéger.
*/

Route::apiResource('sources', SourceController::class);
Route::apiResource('categories', CategoryController::class)->only(['index', 'show']);
Route::apiResource('rss-feeds', RssFeedController::class);
Route::apiResource('rss-items', RssItemController::class)->only(['index', 'show']);

Route::prefix('articles')->group(function () {
    Route::post('generate', [ArticleController::class, 'generate'])->name('articles.generate');
    Route::post('generate-async', [ArticleController::class, 'generateAsync'])->name('articles.generate-async');
    Route::post('{article}/publish', [ArticleController::class, 'publish'])->name('articles.publish');
});
Route::apiResource('articles', ArticleController::class);

Route::get('stats', [StatsController::class, 'index'])->name('stats.index');
