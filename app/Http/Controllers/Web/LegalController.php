<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class LegalController extends Controller
{
    public function mentions(Request $request): Response
    {
        return $this->renderLegal(
            $request,
            'mentions_legales',
            ['fr' => 'Mentions légales', 'nl' => 'Wettelijke vermeldingen'],
            '/mentions-legales'
        );
    }

    public function confidentialite(Request $request): Response
    {
        return $this->renderLegal(
            $request,
            'politique_confidentialite',
            ['fr' => 'Politique de confidentialité', 'nl' => 'Privacybeleid'],
            '/politique-confidentialite'
        );
    }

    public function cookies(Request $request): Response
    {
        return $this->renderLegal(
            $request,
            'politique_cookies',
            ['fr' => 'Politique de cookies', 'nl' => 'Cookiebeleid'],
            '/politique-cookies'
        );
    }

    /**
     * @param  array{fr: string, nl: string}  $titles
     */
    private function renderLegal(Request $request, string $view, array $titles, string $canonical): Response
    {
        $locale = content_locale($request);
        $title = $titles[$locale] ?? $titles['fr'];
        $metaPrefix = $locale === 'nl' ? ' van de Vivat-website.' : ' du site Vivat.';

        $content = render_php_view('site.legal.' . $view, ['locale' => $locale]);

        $html = render_php_view('site.layout', [
            'content'        => $content,
            'content_locale' => $locale,
            'title'          => $title . ' Vivat',
            'meta_description' => $title . $metaPrefix,
            'canonical_url'  => url($canonical),
            'hide_cta_section' => true,
        ]);

        return response($html, 200, ['Content-Type' => 'text/html; charset=UTF-8']);
    }
}
