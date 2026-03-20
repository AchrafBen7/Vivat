<?php

use App\Http\Controllers\Web\ArticleController as WebArticleController;
use App\Http\Controllers\Web\AuthController as WebAuthController;
use App\Http\Controllers\Web\CategoryController as WebCategoryController;
use App\Http\Controllers\Web\ContactController as WebContactController;
use App\Http\Controllers\Web\ContributorController as WebContributorController;
use App\Http\Controllers\Web\FaqController as WebFaqController;
use App\Http\Controllers\Web\HomeController as WebHomeController;
use App\Http\Controllers\Web\SearchController as WebSearchController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Site public — HTML rendu côté serveur (templates PHP, Tailwind)
| Pas de Blade. SEO-friendly.
|--------------------------------------------------------------------------
*/
Route::get('/', WebHomeController::class)->name('home');
Route::get('/register', [WebAuthController::class, 'showRegisterForm'])->name('register');
Route::post('/register', [WebAuthController::class, 'register']);
Route::get('/login', [WebAuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [WebAuthController::class, 'login']);
Route::post('/logout', [WebAuthController::class, 'logout'])->name('logout');
Route::get('/search', [WebSearchController::class, 'index'])->name('search');
Route::get('/contact', [WebContactController::class, 'index'])->name('contact');
Route::get('/faq', [WebFaqController::class, 'index'])->name('faq');

Route::middleware(['auth', 'role:contributor|admin'])->prefix('contributor')->group(function () {
    Route::get('/dashboard', [WebContributorController::class, 'dashboard'])->name('contributor.dashboard');
    Route::match(['get', 'post'], '/new', [WebContributorController::class, 'newArticle'])->name('contributor.new');
    Route::match(['get', 'post'], '/profile', [WebContributorController::class, 'profile'])->name('contributor.profile');
});
Route::get('/categories/{slug}', [WebCategoryController::class, 'hub'])->name('categories.hub');
Route::get('/articles', [WebArticleController::class, 'index'])->name('articles.index');
Route::get('/articles/{slug}', [WebArticleController::class, 'show'])->name('articles.show');
