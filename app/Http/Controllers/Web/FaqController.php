<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class FaqController extends Controller
{
    public function index(Request $request): Response
    {
        $locale = content_locale($request);

        $content = render_php_view('site.faq', [
            'locale' => $locale,
        ]);

        $html = render_php_view('site.layout', [
            'content' => $content,
            'content_locale' => $locale,
            'title' => 'FAQ — Vivat',
            'meta_description' => 'Questions fréquentes sur Vivat, la lecture des articles, la newsletter et la contribution au site.',
            'canonical_url' => url('/faq'),
        ]);

        return response($html, 200, ['Content-Type' => 'text/html; charset=UTF-8']);
    }
}
