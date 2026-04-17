<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class EmailVerificationController extends Controller
{
    public function notice(Request $request): Response|RedirectResponse
    {
        if ($request->user()?->hasVerifiedEmail()) {
            return redirect()->route('contributor.dashboard');
        }

        $content = render_php_view('site.verify_email', [
            'user' => $request->user(),
            'resent' => session('status') === 'verification-link-sent',
        ]);

        $html = render_php_view('site.layout', [
            'content' => $content,
            'content_locale' => content_locale($request),
            'title' => 'Vérifiez votre email Vivat',
            'meta_description' => 'Confirmez votre adresse email pour activer pleinement votre espace rédacteur Vivat.',
            'hide_cta_section' => true,
            'hide_footer' => true,
            'trim_main_bottom' => true,
        ]);

        return response($html, 200, ['Content-Type' => 'text/html; charset=UTF-8']);
    }

    public function verify(EmailVerificationRequest $request): RedirectResponse
    {
        $request->fulfill();

        return redirect()
            ->route('contributor.dashboard')
            ->with('success', __('site.flash_email_verified'));
    }

    public function resend(Request $request): RedirectResponse
    {
        if ($request->user()?->hasVerifiedEmail()) {
            return redirect()->route('contributor.dashboard');
        }

        $request->user()?->sendEmailVerificationNotification();

        return back()->with('status', 'verification-link-sent');
    }
}
