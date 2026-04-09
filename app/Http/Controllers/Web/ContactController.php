<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ContactController extends Controller
{
    public function index(Request $request): Response
    {
        $locale = content_locale($request);

        $content = render_php_view('site.contact', [
            'locale' => $locale,
        ]);

        $html = render_php_view('site.layout', [
            'content' => $content,
            'content_locale' => $locale,
            'title' => 'Contact Vivat',
            'meta_description' => 'Contactez la rédaction Vivat, partagez une question, une suggestion ou une demande de partenariat.',
            'canonical_url' => url('/contact'),
            'hide_cta_section' => true,
        ]);

        return response($html, 200, ['Content-Type' => 'text/html; charset=UTF-8']);
    }
}
