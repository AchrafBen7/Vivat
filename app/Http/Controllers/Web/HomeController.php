<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\PublicPageDataService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class HomeController extends Controller
{
    public function __invoke(Request $request, PublicPageDataService $pageData): Response
    {
        $locale = content_locale($request);
        $data = $pageData->getHomeData($locale);
        $data['writer_signup_url'] = config('vivat.writer_signup_url', '/register');
        $data['writer_dashboard_url'] = url('/contributor/dashboard');
        $isContributor = $request->user() && $request->user()->hasRole(['contributor', 'admin']);
        $data['writer_cta_url'] = $isContributor ? url('/contributor/dashboard') : config('vivat.writer_signup_url', '/register');
        $data['writer_cta_label'] = $isContributor ? 'Accéder au bureau' : 'Rédigez un article';
        $data['writer_cta_description'] = $isContributor ? 'Accédez à votre espace rédacteur.' : 'Écrivez sur Vivat. Votre voix compte.';

        $content = render_php_view('site.home', $data);
        $html = render_php_view('site.layout', [
            'content' => $content,
            'content_locale' => $locale,
            'title' => 'Vivat — Actualités',
            'meta_description' => 'Vivat — Actualités et articles. Découvrez nos rubriques, derniers articles et actualités.',
            'canonical_url' => url('/'),
        ]);

        return response($html, 200, ['Content-Type' => 'text/html; charset=UTF-8']);
    }
}
