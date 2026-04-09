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
        $translate = static fn (string $key, string $fallback): string => __($key) !== $key ? __($key) : $fallback;
        $data['writer_signup_url'] = config('vivat.writer_signup_url', '/register');
        $data['writer_dashboard_url'] = url('/contributor/dashboard');
        $isContributor = $request->user() && $request->user()->hasRole(['contributor', 'admin']);
        $data['writer_cta_url'] = $isContributor ? url('/contributor/dashboard') : config('vivat.writer_signup_url', '/register');
        $data['writer_cta_label'] = $isContributor
            ? $translate('site.writer_cta_dashboard_label', 'Accéder au bureau')
            : $translate('site.writer_cta_guest_label', 'Rédigez un article');
        $data['writer_cta_description'] = $isContributor
            ? $translate('site.writer_cta_dashboard_description', 'Accédez à votre espace rédacteur.')
            : $translate('site.writer_cta_guest_description', 'Écrivez sur Vivat. Votre voix compte.');
        $data['writer_cta_title'] = $isContributor
            ? $translate('site.writer_cta_dashboard_title', 'Accédez à votre espace rédacteur')
            : $translate('site.writer_cta_guest_title', 'Écrivez sur Vivat.');
        $data['writer_cta_subtitle'] = $isContributor
            ? $translate('site.writer_cta_dashboard_subtitle', 'Gérez vos articles et vos soumissions.')
            : $translate('site.writer_cta_guest_subtitle', 'Votre voix compte.');
        $data['writer_cta_tag_1'] = $translate('site.writer_cta_tag_1', 'Rédaction');
        $data['writer_cta_tag_2'] = $translate('site.writer_cta_tag_2', 'Actualités');
        $data['writer_cta_secondary_label'] = $isContributor
            ? $translate('site.writer_cta_dashboard_secondary_label', 'Mes soumissions')
            : $translate('site.writer_cta_guest_secondary_label', 'En savoir plus');
        $data['writer_cta_secondary_url'] = $isContributor ? url('/contributor/submissions') : url('/devenir-redacteur');

        $content = render_php_view('site.home', $data);
        $html = render_php_view('site.layout', [
            'content' => $content,
            'content_locale' => $locale,
            'title' => 'Vivat Actualités',
            'meta_description' => 'Vivat Actualités et articles. Découvrez nos rubriques, derniers articles et actualités.',
            'canonical_url' => url('/'),
        ]);

        return response($html, 200, ['Content-Type' => 'text/html; charset=UTF-8']);
    }
}
