<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\NewsletterSubscriptionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class NewsletterController extends Controller
{
    public function __construct(
        private readonly NewsletterSubscriptionService $newsletterSubscriptionService,
    ) {}

    public function subscribe(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email', 'max:255'],
        ]);

        $result = $this->newsletterSubscriptionService->subscribe([
            'email' => $validated['email'],
        ]);

        return back()->with('success', $result['message']);
    }

    public function confirm(Request $request): Response
    {
        $result = $this->newsletterSubscriptionService->confirm($request->query('token'));

        return $this->renderStatusPage(
            title: $result['status'] === 'confirmed' ? 'Newsletter confirmée' : 'Confirmation impossible',
            heading: $result['status'] === 'confirmed' ? 'Bienvenue dans la newsletter Vivat' : 'Lien invalide',
            message: $result['message'],
            success: $result['status'] === 'confirmed',
        );
    }

    public function unsubscribe(Request $request): Response
    {
        $result = $this->newsletterSubscriptionService->unsubscribe($request->query('token'));

        return $this->renderStatusPage(
            title: $result['status'] === 'unsubscribed' ? 'Désinscription confirmée' : 'Désinscription impossible',
            heading: $result['status'] === 'unsubscribed' ? 'Vous êtes désinscrit' : 'Lien invalide',
            message: $result['message'],
            success: $result['status'] === 'unsubscribed',
        );
    }

    private function renderStatusPage(string $title, string $heading, string $message, bool $success): Response
    {
        $content = render_php_view('site.newsletter_status', [
            'heading' => $heading,
            'message' => $message,
            'success' => $success,
        ]);

        $html = render_php_view('site.layout', [
            'content' => $content,
            'content_locale' => content_locale(request()),
            'title' => $title . ' — Vivat',
            'meta_description' => $message,
        ]);

        return response($html, 200, ['Content-Type' => 'text/html; charset=UTF-8']);
    }
}
