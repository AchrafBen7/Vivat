<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class LegalController extends Controller
{
    public function mentions(Request $request): Response
    {
        return $this->renderLegal($request, 'mentions_legales', 'Mentions légales', '/mentions-legales');
    }

    public function confidentialite(Request $request): Response
    {
        return $this->renderLegal($request, 'politique_confidentialite', 'Politique de confidentialité', '/politique-confidentialite');
    }

    public function cookies(Request $request): Response
    {
        return $this->renderLegal($request, 'politique_cookies', 'Politique de cookies', '/politique-cookies');
    }

    private function renderLegal(Request $request, string $view, string $title, string $canonical): Response
    {
        $locale = content_locale($request);

        $content = render_php_view('site.legal.' . $view, ['locale' => $locale]);

        $html = render_php_view('site.layout', [
            'content'        => $content,
            'content_locale' => $locale,
            'title'          => $title . ' — Vivat',
            'meta_description' => $title . ' du site Vivat.',
            'canonical_url'  => url($canonical),
            'hide_cta_section' => true,
        ]);

        return response($html, 200, ['Content-Type' => 'text/html; charset=UTF-8']);
    }
}
