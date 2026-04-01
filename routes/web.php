<?php

use App\Http\Controllers\Web\AboutController as WebAboutController;
use App\Http\Controllers\Web\LegalController as WebLegalController;
use App\Http\Controllers\Web\SitemapController as WebSitemapController;
use App\Http\Controllers\Web\PublicFeedController as WebPublicFeedController;
use App\Http\Controllers\Web\ArticleController as WebArticleController;
use App\Http\Controllers\Web\AuthController as WebAuthController;
use App\Http\Controllers\Web\CategoryController as WebCategoryController;
use App\Http\Controllers\Web\ContactController as WebContactController;
use App\Http\Controllers\Web\ContributorController as WebContributorController;
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
Route::get('/sitemap.xml', WebSitemapController::class)->name('sitemap');
Route::get('/feed.xml', WebPublicFeedController::class)->name('feed.rss');
Route::get('/', WebHomeController::class)->name('home');
Route::get('/devenir-redacteur', [WebAuthController::class, 'showBecomeContributor'])->name('become.contributor');
Route::get('/register', [WebAuthController::class, 'showRegisterForm'])->name('register');
Route::post('/register', [WebAuthController::class, 'register'])->middleware(['bot.protect:web', 'throttle:auth-register']);
Route::get('/login', [WebAuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [WebAuthController::class, 'login'])->middleware(['bot.protect:web', 'throttle:auth-login']);
Route::get('/auth/google/redirect', [WebAuthController::class, 'redirectToGoogle'])->middleware('throttle:auth-login')->name('auth.google.redirect');
Route::get('/auth/google/callback', [WebAuthController::class, 'handleGoogleCallback'])->name('auth.google.callback');
Route::get('/forgot-password', [WebAuthController::class, 'showForgotPasswordForm'])->name('password.request');
Route::post('/forgot-password', [WebAuthController::class, 'sendResetLink'])->middleware(['bot.protect:web', 'throttle:password-reset-link'])->name('password.email');
Route::get('/reset-password/{token}', [WebAuthController::class, 'showResetPasswordForm'])->name('password.reset');
Route::post('/reset-password', [WebAuthController::class, 'resetPassword'])->name('password.update');
Route::post('/logout', [WebAuthController::class, 'logout'])->name('logout');
Route::get('/search/suggestions', [WebSearchController::class, 'suggestions'])->middleware(['bot.protect:web', 'throttle:search-suggestions'])->name('search.suggestions');
Route::get('/search', [WebSearchController::class, 'index'])->name('search');
Route::get('/contact', [WebContactController::class, 'index'])->name('contact');
Route::get('/a-propos', WebAboutController::class)->name('about');
Route::get('/mentions-legales', [WebLegalController::class, 'mentions'])->name('legal.mentions');
Route::get('/politique-confidentialite', [WebLegalController::class, 'confidentialite'])->name('legal.confidentialite');
Route::get('/politique-cookies', [WebLegalController::class, 'cookies'])->name('legal.cookies');
Route::get('/faq', [WebFaqController::class, 'index'])->name('faq');
Route::post('/newsletter/subscribe', [WebNewsletterController::class, 'subscribe'])->middleware(['bot.protect:web', 'throttle:newsletter-subscribe'])->name('newsletter.subscribe.web');
Route::get('/newsletter/confirm', [WebNewsletterController::class, 'confirm'])->name('newsletter.confirm');
Route::get('/newsletter/unsubscribe', [WebNewsletterController::class, 'unsubscribe'])->name('newsletter.unsubscribe');

Route::middleware(['auth', 'role:contributor|admin'])->prefix('contributor')->group(function () {
    Route::get('/dashboard', [WebContributorController::class, 'dashboard'])->name('contributor.dashboard');
    Route::match(['get', 'post'], '/new', [WebContributorController::class, 'newArticle'])->name('contributor.new');
    Route::get('/payments', [WebContributorController::class, 'paymentsHistory'])->name('contributor.payments.history');
    Route::post('/payments/create-intent', [ApiPaymentController::class, 'createIntent'])->middleware('throttle:payment-actions')->name('contributor.web-payments.create-intent');
    Route::post('/payments/confirm', [ApiPaymentController::class, 'confirm'])->middleware('throttle:payment-actions')->name('contributor.web-payments.confirm');
    Route::get('/payments/{payment}/refund-receipt', [WebContributorController::class, 'refundReceipt'])->name('contributor.payments.refund-receipt');
    Route::get('/articles/{submission:slug}', [WebContributorController::class, 'showSubmission'])->name('contributor.articles.show');
    Route::match(['get', 'post'], '/articles/{submission:slug}/edit', [WebContributorController::class, 'editSubmission'])->name('contributor.articles.edit');
    Route::post('/articles/{submission:slug}/request-unpublish', [WebContributorController::class, 'requestUnpublish'])->name('contributor.articles.request-unpublish');
    Route::delete('/articles/{submission:slug}', [WebContributorController::class, 'destroySubmission'])->name('contributor.articles.destroy');
    Route::match(['get', 'post'], '/profile', [WebContributorController::class, 'profile'])->name('contributor.profile');
});
Route::get('/categories/{slug}', [WebCategoryController::class, 'hub'])->name('categories.hub');
Route::get('/articles', [WebArticleController::class, 'index'])->name('articles.index');
Route::get('/articles/{slug}', [WebArticleController::class, 'show'])->name('articles.show');
