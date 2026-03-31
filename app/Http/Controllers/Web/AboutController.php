<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class AboutController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $locale = content_locale($request);

        $content = render_php_view('site.about', [
            'locale' => $locale,
        ]);

        $html = render_php_view('site.layout', [
            'content' => $content,
            'content_locale' => $locale,
            'title' => 'À propos de Vivat',
            'meta_description' => 'Vivat, magazine en ligne centré sur la durabilité et le « vivre mieux », avec des éditions en français et en néerlandais et de nombreuses rubriques.',
            'canonical_url' => url('/a-propos'),
            'hide_cta_section' => true,
        ]);

        return response($html, 200, ['Content-Type' => 'text/html; charset=UTF-8']);
    }
}
