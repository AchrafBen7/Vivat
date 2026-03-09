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
        $data = $pageData->getHomeData();
        $data['writer_signup_url'] = config('vivat.writer_signup_url', '/register');
        $data['writer_dashboard_url'] = config('vivat.writer_dashboard_url', '/contributor/submissions');

        $content = render_php_view('site.home', $data);
        $html = render_php_view('site.layout', [
            'content' => $content,
            'title' => 'Vivat — Actualités',
            'meta_description' => 'Vivat — Actualités et articles. Découvrez nos rubriques, derniers articles et actualités.',
            'canonical_url' => url('/'),
        ]);

        return response($html, 200, ['Content-Type' => 'text/html; charset=UTF-8']);
    }
}
