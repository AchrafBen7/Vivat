<?php

use App\Http\Controllers\Web\ArticleController as WebArticleController;
use App\Http\Controllers\Web\AuthController as WebAuthController;
use App\Http\Controllers\Web\CategoryController as WebCategoryController;
use App\Http\Controllers\Web\ContactController as WebContactController;
use App\Http\Controllers\Web\ContributorController as WebContributorController;
use App\Http\Controllers\Web\EmailVerificationController as WebEmailVerificationController;
use App\Http\Controllers\Web\FaqController as WebFaqController;
use App\Http\Controllers\Web\HomeController as WebHomeController;
use App\Http\Controllers\Web\NewsletterController as WebNewsletterController;
use App\Http\Controllers\Web\SearchController as WebSearchController;
use App\Http\Controllers\Api\PaymentController as ApiPaymentController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Site public — HTML rendu côté serveur (templates PHP, Tailwind)
| Pas de Blade. SEO-friendly.
|--------------------------------------------------------------------------
*/
Route::get('/', WebHomeController::class)->name('home');
Route::get('/devenir-redacteur', [WebAuthController::class, 'showBecomeContributor'])->name('become.contributor');
Route::get('/register', [WebAuthController::class, 'showRegisterForm'])->name('register');
Route::post('/register', [WebAuthController::class, 'register'])->middleware('throttle:auth-register');
Route::get('/login', [WebAuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [WebAuthController::class, 'login'])->middleware('throttle:auth-login');
Route::post('/logout', [WebAuthController::class, 'logout'])->name('logout');
Route::get('/logout', [WebAuthController::class, 'logout'])->name('logout.get');
Route::get('/search/suggestions', [WebSearchController::class, 'suggestions'])->name('search.suggestions');
Route::get('/search', [WebSearchController::class, 'index'])->name('search');
Route::get('/contact', [WebContactController::class, 'index'])->name('contact');
Route::get('/faq', [WebFaqController::class, 'index'])->name('faq');
Route::post('/newsletter/subscribe', [WebNewsletterController::class, 'subscribe'])->middleware('throttle:newsletter-subscribe')->name('newsletter.subscribe.web');
Route::get('/newsletter/confirm', [WebNewsletterController::class, 'confirm'])->name('newsletter.confirm');
Route::get('/newsletter/unsubscribe', [WebNewsletterController::class, 'unsubscribe'])->name('newsletter.unsubscribe');
Route::get('/email/verify', [WebEmailVerificationController::class, 'notice'])->middleware('auth')->name('verification.notice');
Route::get('/email/verify/{id}/{hash}', [WebEmailVerificationController::class, 'verify'])->middleware(['auth', 'signed', 'throttle:6,1'])->name('verification.verify');
Route::post('/email/verification-notification', [WebEmailVerificationController::class, 'resend'])->middleware(['auth', 'throttle:6,1'])->name('verification.send');

Route::middleware(['auth', 'verified', 'role:contributor|admin'])->prefix('contributor')->group(function () {
    Route::get('/dashboard', [WebContributorController::class, 'dashboard'])->name('contributor.dashboard');
    Route::match(['get', 'post'], '/new', [WebContributorController::class, 'newArticle'])->name('contributor.new');
    Route::post('/payments/create-intent', [ApiPaymentController::class, 'createIntent'])->middleware('throttle:payment-actions')->name('contributor.web-payments.create-intent');
    Route::post('/payments/confirm', [ApiPaymentController::class, 'confirm'])->middleware('throttle:payment-actions')->name('contributor.web-payments.confirm');
    Route::get('/articles/{submission:slug}', [WebContributorController::class, 'showSubmission'])->name('contributor.articles.show');
    Route::match(['get', 'post'], '/articles/{submission:slug}/edit', [WebContributorController::class, 'editSubmission'])->name('contributor.articles.edit');
    Route::delete('/articles/{submission:slug}', [WebContributorController::class, 'destroySubmission'])->name('contributor.articles.destroy');
    Route::match(['get', 'post'], '/profile', [WebContributorController::class, 'profile'])->name('contributor.profile');
});
Route::get('/categories/{slug}', [WebCategoryController::class, 'hub'])->name('categories.hub');
Route::get('/articles', [WebArticleController::class, 'index'])->name('articles.index');
Route::get('/articles/{slug}', [WebArticleController::class, 'show'])->name('articles.show');
