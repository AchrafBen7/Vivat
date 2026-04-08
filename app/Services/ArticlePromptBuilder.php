<?php

namespace App\Services;

use App\Models\CategoryTemplate;
use App\Models\RssItem;
use Illuminate\Support\Str;

class ArticlePromptBuilder
{
    public function buildSystemPrompt(
        ?CategoryTemplate $template,
        ?string $articleType = null,
        ?int $minWordsOverride = null,
        ?int $maxWordsOverride = null
    ): string {
        $articleTypesConfig = config('selection.article_types', []);
        $typeConfig = $articleType && isset($articleTypesConfig[$articleType])
            ? $articleTypesConfig[$articleType]
            : ($articleTypesConfig['standard'] ?? []);

        $minWords = $minWordsOverride ?? $template?->min_word_count ?? $typeConfig['min_words'] ?? 900;
        $maxWords = $maxWordsOverride ?? $template?->max_word_count ?? $typeConfig['max_words'] ?? 2000;
        $tone = $template?->tone ?? $typeConfig['tone'] ?? 'professionnel et accessible';
        $structure = $template?->structure ?? 'standard';
        $styleNotes = $template?->style_notes ?? '';
        $seoRules = $template?->seo_rules ?? '';

        $typeInstruction = '';
        if ($articleType === 'hot_news') {
            $typeInstruction = "\nTYPE D'ARTICLE : Brève / actualité chaude. Style percutant, direct, factuel. Titre accrocheur. Pas de développement long.";
        } elseif ($articleType === 'long_form') {
            $typeInstruction = "\nTYPE D'ARTICLE : Article de fond. Approfondi, analytique, bien structuré avec sous-parties. Contexte et mise en perspective.";
        }

        return <<<PROMPT
Tu es un rédacteur expert en contenu SEO. Génère un article de synthèse 100% original à partir des sources fournies.

RÈGLES :
- Ton : {$tone}. Structure : {$structure}.
- Longueur OBLIGATOIRE : entre {$minWords} et {$maxWords} mots (respecte cette fourchette).
- Contenu HTML avec h2/h3 bien structurés. Chaque section apporte de la valeur.
- Le titre doit contenir le mot-clé principal et être accrocheur (50-65 caractères idéal).
- Le premier paragraphe doit contenir le mot-clé principal naturellement.
- Utiliser les mots-clés SEO fournis naturellement dans le texte (densité 1-2%).
- meta_title : 50-60 caractères, contient le mot-clé principal.
- meta_description : 150-160 caractères, incitative au clic.
- keywords : mots-clés longue traîne et spécifiques (pas de termes génériques).
{$typeInstruction}

RÉPONSE UNIQUEMENT en JSON :
{
  "title": "...",
  "excerpt": "...",
  "content": "<h2>...</h2><p>...</p>...",
  "meta_title": "...",
  "meta_description": "...",
  "keywords": ["mot-clé 1", "mot-clé 2", ...]
}
{$styleNotes}
{$seoRules}
PROMPT;
    }

    /**
     * @param  \Illuminate\Database\Eloquent\Collection<int, RssItem>  $items
     */
    public function buildUserPrompt($items, ?string $customPrompt, ?string $contextPriority = null): string
    {
        $parts = [];
        $allSeoKeywords = [];

        foreach ($items as $item) {
            $enriched = $item->enrichedItem;
            $sourceName = $item->rssFeed?->source?->name ?? 'Source inconnue';

            $seoKw = $enriched->seo_keywords ?? [];
            $allSeoKeywords = array_merge($allSeoKeywords, $seoKw);

            $part = "## Source : {$item->title} ({$sourceName})";
            $part .= "\nURL : {$item->url}";
            $part .= "\nSujet principal : " . ($enriched->primary_topic ?? 'Non défini');
            $part .= "\nLead : " . ($enriched->lead ?? '');
            $part .= "\nPoints clés : " . json_encode($enriched->key_points ?? []);
            if (! empty($seoKw)) {
                $part .= "\nMots-clés SEO identifiés : " . implode(', ', $seoKw);
            }
            $part .= "\nTexte extrait : " . Str::limit($enriched->extracted_text ?? '', 2000);
            $parts[] = $part;
        }

        $sources = implode("\n\n---\n\n", $parts);

        $contextBlock = '';
        if ($contextPriority !== null && $contextPriority !== '') {
            $contextBlock = "\n\n## Contexte de priorité (base-toi sur ceci pour le choix éditorial) :\n"
                . $contextPriority
                . "\n\nCe sujet est prioritaire ; l'article doit refléter cette importance.";
        }

        $uniqueKeywords = array_unique($allSeoKeywords);
        $seoSection = '';
        if (! empty($uniqueKeywords)) {
            $seoSection = "\n\n## Mots-clés SEO à intégrer (par ordre de priorité) :\n"
                . implode(', ', array_slice($uniqueKeywords, 0, 15))
                . "\n\nUtilise ces mots-clés naturellement dans le titre, les sous-titres et le corps du texte.";
        }

        $custom = $customPrompt ? "\n\n## Instructions supplémentaires :\n" . $customPrompt : '';

        return "# Sources à synthétiser ({$items->count()} articles de {$items->pluck('rssFeed.source.name')->filter()->unique()->implode(', ')})\n\n"
            . $sources
            . $contextBlock
            . $seoSection
            . $custom;
    }

    public function buildCoverPrompt(string $title, string $excerpt, ?string $categoryId): string
    {
        $categoryName = $categoryId ? (\App\Models\Category::find($categoryId)?->name ?? '') : '';
        $summary = trim(Str::limit(strip_tags($excerpt), 240, ''));

        $sceneHint = $this->coverSceneHint($title, $summary, $categoryName);
        $negativeHint = $this->coverNegativeHint($title, $summary, $categoryName);

        $prompt = 'Realistic editorial magazine photography for a Belgian media article. ';
        $prompt .= 'Natural light, authentic everyday setting, credible composition, horizontal cover image, subtle photojournalistic style. ';
        $prompt .= 'The image must directly represent the article topic with a concrete real-world scene, not a generic travel or stock image. ';
        $prompt .= 'No text, no letters, no logo, no watermark, no illustration, no 3D, no fake glossy advertising look, no obvious AI look, no plastic skin, no distorted hands. ';
        $prompt .= 'The image must look like a genuine candid photo selected by an editor. ';
        $prompt .= 'Article title: "' . $title . '". ';

        if ($categoryName !== '') {
            $prompt .= 'Category: ' . $categoryName . '. ';
        }

        if ($summary !== '') {
            $prompt .= 'Article summary: ' . $summary . '. ';
        }

        if ($sceneHint !== '') {
            $prompt .= 'Preferred scene: ' . $sceneHint . '. ';
        }

        if ($negativeHint !== '') {
            $prompt .= 'Avoid: ' . $negativeHint . '. ';
        }

        return Str::limit($prompt, 1200, '');
    }

    private function coverSceneHint(string $title, string $summary, string $categoryName): string
    {
        $text = mb_strtolower($title . ' ' . $summary . ' ' . $categoryName);

        return match (true) {
            str_contains($text, 'sobriété énergétique'),
            str_contains($text, 'empreinte'),
            str_contains($text, 'énergie'),
            str_contains($text, 'consommation') => 'a realistic home interior in Belgium with simple energy-saving gestures, such as adjusting a thermostat, switching off lights, insulating windows, or reducing electricity use',

            str_contains($text, 'finance'),
            str_contains($text, 'budget'),
            str_contains($text, 'épargne') => 'a realistic everyday financial scene, such as a person reviewing household expenses, using a calculator, or managing bills at a kitchen table',

            str_contains($text, 'santé'),
            str_contains($text, 'bien-être') => 'a natural health-related daily life scene, calm and credible, without hospital drama or exaggerated medical imagery',

            str_contains($text, 'voyage') => 'a realistic local travel moment in Europe or Belgium, natural and understated, not luxury tourism',

            str_contains($text, 'technologie') => 'a realistic modern tech usage scene in daily life, subtle and credible, without futuristic sci-fi aesthetics',

            str_contains($text, 'famille') => 'a natural family daily-life scene, authentic and warm, without posed studio look',

            str_contains($text, 'maison'),
            str_contains($text, 'chez soi'),
            str_contains($text, 'habitat') => 'a realistic home and living scene, useful and grounded in everyday life',

            default => 'a realistic editorial photo linked directly to the article subject, grounded in everyday life in Belgium',
        };
    }

    private function coverNegativeHint(string $title, string $summary, string $categoryName): string
    {
        $text = mb_strtolower($title . ' ' . $summary . ' ' . $categoryName);

        $avoid = ['tropical beach', 'luxury resort', 'vacation postcard', 'fantasy scenery', 'generic sunset stock photo'];

        if (str_contains($text, 'énergie') || str_contains($text, 'sobriété énergétique') || str_contains($text, 'empreinte')) {
            $avoid[] = 'travel imagery';
            $avoid[] = 'holiday atmosphere';
        }

        if (str_contains($text, 'finance') || str_contains($text, 'budget')) {
            $avoid[] = 'gold bars';
            $avoid[] = 'cartoon money symbolism';
        }

        return implode(', ', $avoid);
    }
}
