<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\PublicPageDataService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class SearchController extends Controller
{
    public function index(Request $request): Response
    {
        $locale = content_locale($request);
        $q = trim((string) $request->get('q', ''));

        $data = app(PublicPageDataService::class)->getSearchData($locale, $q);
        $content = render_php_view('site.search', $data);

        $title = $q !== ''
            ? sprintf('Recherche : %s — Vivat', htmlspecialchars($q))
            : 'Recherche — Vivat';
        $metaDescription = $q !== ''
            ? sprintf('Résultats de recherche pour « %s » sur Vivat.', htmlspecialchars($q))
            : 'Recherchez des articles et actualités par mot-clé ou par catégorie.';

        $html = render_php_view('site.layout', [
            'content' => $content,
            'content_locale' => $locale,
            'title' => $title,
            'meta_description' => $metaDescription,
            'canonical_url' => url('/search' . ($q !== '' ? '?q=' . rawurlencode($q) : '')),
        ]);

        return response($html, 200, ['Content-Type' => 'text/html; charset=UTF-8']);
    }
}
