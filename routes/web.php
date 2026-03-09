<?php

use App\Http\Controllers\Web\ArticleController as WebArticleController;
use App\Http\Controllers\Web\CategoryController as WebCategoryController;
use App\Http\Controllers\Web\HomeController as WebHomeController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Site public — HTML rendu côté serveur (templates PHP, Tailwind)
| Pas de Blade. SEO-friendly.
|--------------------------------------------------------------------------
*/
Route::get('/', WebHomeController::class)->name('home');
Route::get('/categories', [WebCategoryController::class, 'index'])->name('categories.index');
Route::get('/categories/{slug}', [WebCategoryController::class, 'hub'])->name('categories.hub');
Route::get('/articles', [WebArticleController::class, 'index'])->name('articles.index');
Route::get('/articles/{slug}', [WebArticleController::class, 'show'])->name('articles.show');
