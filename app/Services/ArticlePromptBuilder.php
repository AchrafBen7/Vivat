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
Tu es un rédacteur expert en contenu SEO pour un média belge francophone. Tu produis un article de synthèse éditorial à partir de plusieurs sources journalistiques.

═══════════════════════════════════════════
RÈGLES ABSOLUES PROPRIÉTÉ INTELLECTUELLE
═══════════════════════════════════════════
1. PARAPHRASE OBLIGATOIRE : Reformule chaque idée avec tes propres mots et ta propre structure de phrase. Ne reprends JAMAIS une phrase telle quelle, même partiellement.
2. INTERDIT DE COPIER : Plus de 3 mots consécutifs d'une source = violation. Construis des phrases entièrement nouvelles.
3. TRANSFORMATION SÉMANTIQUE : Exprime les mêmes faits avec un angle, un ordre ou une formulation différents de chaque source.
4. DONNÉES FACTUELLES : Les chiffres, dates et faits précis peuvent être repris s'ils sont reformulés avec une attribution ("selon X", "d'après Y").
5. AJOUT DE VALEUR : L'article doit apporter un éclairage, une mise en contexte ou une synthèse que chaque source prise séparément n'offre pas.
6. JAMAIS de citation directe encadrée par des guillemets sauf si c'est une déclaration officielle nominative et indispensable.

═══════════════════
RÈGLES ÉDITORIALES
═══════════════════
- Ton : {$tone}. Structure : {$structure}.
- Longueur OBLIGATOIRE : entre {$minWords} et {$maxWords} mots.
- Contenu HTML valide : h2 / h3, paragraphes <p>, listes <ul> si pertinent.
- Le titre contient le mot-clé principal, accrocheur (50-65 caractères idéal).
- Le premier paragraphe contient le mot-clé principal naturellement.
- Mots-clés SEO intégrés naturellement (densité 1-2%).
- meta_title : 50-60 caractères avec mot-clé principal.
- meta_description : 150-160 caractères, incitative au clic.
- keywords : mots-clés longue traîne spécifiques (pas de termes génériques).
{$typeInstruction}

═══════════════
FORMAT DE SORTIE
═══════════════
RÉPONSE UNIQUEMENT en JSON valide :
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
        $summary = trim(Str::limit(strip_tags($excerpt), 300, ''));

        // Étape 1 : GPT-4o extrait un sujet visuel unique et concret
        $visualSubject = $this->extractVisualSubject($title, $summary, $categoryName);

        // Étape 2 : prompt de génération minimaliste basé sur ce sujet
        $prompt  = 'Real documentary photograph of: ' . $visualSubject . '. ';
        $prompt .= 'Camera: Sony A7 or Canon R6, 50mm lens, f/2.0, ISO 400, handheld. ';
        $prompt .= 'Eye-level shot, standing distance, casual framing — NOT aerial, NOT drone, NOT bird\'s-eye view. ';
        $prompt .= 'Overcast natural daylight or soft window light. No sun rays, no lens flare, no golden glow, no HDR. ';
        $prompt .= 'Slight film grain visible. Colors desaturated and muted — like a real press photo, not a commercial shoot. ';
        $prompt .= 'Background blurred by real bokeh (not fake Gaussian blur). Foreground sharp and detailed. ';
        $prompt .= 'The image must look like it was taken by a journalist on assignment, not generated by AI. ';
        $prompt .= 'Forbidden: CGI look, 3D render sharpness, perfect glossy surfaces, architectural visualization style, perfect spherical bushes, dramatic skies, saturated greens, cartoon textures. ';
        $prompt .= 'Forbidden: aerial view, wide establishing shot, multiple buildings, panoramic landscape. ';
        $prompt .= 'Forbidden: text, signs, logos, people, faces, hands.';

        return Str::limit($prompt, 1200, '');
    }

    /**
     * Point d'entrée public pour CoverImageService (provider Pexels).
     */
    public function extractVisualSubjectPublic(string $title, string $excerpt, ?string $categoryId): string
    {
        $categoryName = $categoryId ? (\App\Models\Category::find($categoryId)?->name ?? '') : '';
        $summary      = trim(Str::limit(strip_tags($excerpt), 300, ''));

        return $this->extractVisualSubject($title, $summary, $categoryName);
    }

    /**
     * Utilise GPT-4o mini pour extraire un sujet visuel unique, simple et photographiable
     * depuis le titre et le résumé de l'article.
     */
    private function extractVisualSubject(string $title, string $summary, string $categoryName): string
    {
        $apiKey = config('services.openai.api_key');

        if (! $apiKey) {
            return $title;
        }

        try {
            $response = \Illuminate\Support\Facades\Http::withToken($apiKey)
                ->timeout(20)
                ->post('https://api.openai.com/v1/chat/completions', [
                    'model'       => 'gpt-4o-mini',
                    'temperature' => 0.4,
                    'max_tokens'  => 60,
                    'messages'    => [
                        [
                            'role'    => 'system',
                            'content' => <<<'SYSTEM'
You pick ONE simple, concrete, real-world visual subject for a cover photo.
Rules:
- ONE subject only: one object, one texture, one plant, one material, one place detail, one food item, one animal.
- Must be photographable in real life with a smartphone.
- No people, no faces, no text, no collage of multiple things.
- Choose the most tangible, visual element — not an abstract concept.
- Output a short English photo description (max 15 words), nothing else.
Examples:
  "a stack of oak firewood logs outside a stone farmhouse"
  "close-up of a solar panel surface in afternoon light"
  "recycling bins on a quiet residential sidewalk"
  "a bowl of organic lentils on a wooden kitchen counter"
SYSTEM,
                        ],
                        [
                            'role'    => 'user',
                            'content' => 'Article title: ' . $title
                                . ($summary ? "\nSummary: " . $summary : '')
                                . ($categoryName ? "\nCategory: " . $categoryName : ''),
                        ],
                    ],
                ]);

            $subject = trim($response->json('choices.0.message.content') ?? '');

            if ($subject !== '') {
                return $subject;
            }
        } catch (\Throwable) {
            // Fallback silencieux sur le titre
        }

        return $title;
    }

    private function coverSceneHint(string $title, string $summary, string $categoryName): string
    {
        $text = mb_strtolower($title . ' ' . $summary . ' ' . $categoryName);

        return match (true) {
            str_contains($text, 'homard'),
            str_contains($text, 'crustacé'),
            str_contains($text, 'crustace'),
            str_contains($text, 'atlantique'),
            str_contains($text, 'espèce'),
            str_contains($text, 'espece'),
            str_contains($text, 'scientifiques'),
            str_contains($text, 'biodiversité'),
            str_contains($text, 'biodiversite') => 'a realistic editorial wildlife or marine science scene focused on the animal or natural phenomenon itself, such as a blue lobster in a real marine setting, an aquarium tank, fishing gear, or a coastal environment, without any human subject',

            str_contains($text, 'argiles'),
            str_contains($text, 'invendable'),
            str_contains($text, 'sécheresse'),
            str_contains($text, 'secheresse'),
            str_contains($text, 'fissure'),
            str_contains($text, 'retrait-gonflement') => 'a realistic residential house exterior with visible climate-related risk, such as dry ground, structural cracks, parched garden soil, or a damaged facade, without any person in frame',

            str_contains($text, 'sobriété énergétique'),
            str_contains($text, 'empreinte'),
            str_contains($text, 'énergie'),
            str_contains($text, 'consommation') => 'a realistic Belgian home interior or exterior showing energy use or saving through objects and environment, such as a thermostat, radiator, insulation detail, light switch, window, or meter, without any person in frame',

            str_contains($text, 'finance'),
            str_contains($text, 'budget'),
            str_contains($text, 'épargne') => 'a realistic everyday finance-related still life or environment, such as bills, coins, receipts, a calculator, or a kitchen table scene, without any person in frame',

            str_contains($text, 'santé'),
            str_contains($text, 'bien-être') => 'a natural health-related environment or object scene, calm and credible, without hospital drama, portrait, or exaggerated medical imagery',

            str_contains($text, 'voyage') => 'a realistic local travel or place scene in Europe or Belgium, natural and understated, without tourists posing',

            str_contains($text, 'technologie') => 'a realistic modern tech-related environment or object scene, subtle and credible, without futuristic sci-fi aesthetics or people using devices',

            str_contains($text, 'famille') => 'a realistic family-related environment or object scene suggesting daily life without showing people directly, such as a home setting, toys, school objects, or a kitchen table',

            str_contains($text, 'maison'),
            str_contains($text, 'chez soi'),
            str_contains($text, 'habitat') => 'a realistic home and living scene, useful and grounded in everyday life, without people',

            default => 'a realistic editorial wide shot linked directly to the article subject, grounded in everyday life in Belgium, without people',
        };
    }

    private function coverNegativeHint(string $title, string $summary, string $categoryName): string
    {
        $text = mb_strtolower($title . ' ' . $summary . ' ' . $categoryName);

        $avoid = ['tropical beach', 'luxury resort', 'vacation postcard', 'fantasy scenery', 'generic sunset stock photo'];
        $avoid[] = 'office meeting';
        $avoid[] = 'business team around a table';
        $avoid[] = 'printed charts';
        $avoid[] = 'analytics dashboard';
        $avoid[] = 'tablet with graphs';
        $avoid[] = 'generic coworking scene';
        $avoid[] = 'portrait';
        $avoid[] = 'close-up face';
        $avoid[] = 'people looking at camera';
        $avoid[] = 'text inside the image';
        $avoid[] = 'letters or typographic elements';
        $avoid[] = 'poster or sign with readable words';

        if (str_contains($text, 'énergie') || str_contains($text, 'sobriété énergétique') || str_contains($text, 'empreinte')) {
            $avoid[] = 'travel imagery';
            $avoid[] = 'holiday atmosphere';
        }

        if (str_contains($text, 'finance') || str_contains($text, 'budget')) {
            $avoid[] = 'gold bars';
            $avoid[] = 'cartoon money symbolism';
        }

        if (str_contains($text, 'homard') || str_contains($text, 'atlantique') || str_contains($text, 'scientifiques')) {
            $avoid[] = 'office desk';
            $avoid[] = 'meeting room';
            $avoid[] = 'stock business photo';
        }

        if (str_contains($text, 'maison') || str_contains($text, 'argiles') || str_contains($text, 'invendable')) {
            $avoid[] = 'abstract climate concept art';
            $avoid[] = 'corporate presentation';
        }

        return implode(', ', $avoid);
    }
}
