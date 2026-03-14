<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

class Category extends Model
{
    use HasUuids;

    public $timestamps = false;

    const CREATED_AT = 'created_at';

    protected $fillable = [
        'name',
        'slug',
        'description',
        'home_order',
        'image_url',
        'video_url',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function rssFeeds(): HasMany
    {
        return $this->hasMany(RssFeed::class);
    }

    public function rssItems(): HasMany
    {
        return $this->hasMany(RssItem::class);
    }

    public function clusters(): HasMany
    {
        return $this->hasMany(Cluster::class);
    }

    public function articles(): HasMany
    {
        return $this->hasMany(Article::class);
    }

    public function template(): HasOne
    {
        return $this->hasOne(CategoryTemplate::class);
    }

    public function subCategories(): HasMany
    {
        return $this->hasMany(SubCategory::class, 'category_id')->orderBy('order');
    }

    /**
     * Sous-catégories (filtres) : termes de la description + 2 mots les plus fréquents dans les articles.
     * Les 2 mots ajoutés ne doivent pas être déjà présents dans la description.
     *
     * @return array<int, array{name: string, slug: string}>
     */
    public function getDescriptionSubCategories(): array
    {
        $description = $this->description ?? '';
        $parts = preg_split('/[,;]|\s+et\s+|\s+and\s+/u', $description, -1, PREG_SPLIT_NO_EMPTY);
        $seen = [];
        $out = [];
        $descriptionTermsLower = [];
        foreach ($parts as $part) {
            $name = trim($part);
            if ($name === '') {
                continue;
            }
            $slug = Str::slug($name);
            if ($slug === '' || isset($seen[$slug])) {
                continue;
            }
            $seen[$slug] = true;
            $descriptionTermsLower[] = mb_strtolower($name);
            $out[] = ['name' => $name, 'slug' => $slug];
        }
        $descriptionTermsLower[] = mb_strtolower($this->name ?? '');

        // Ajouter 2 mots les plus fréquents dans les articles, non présents dans la description
        $extra = $this->getTopWordsFromArticles(2, $descriptionTermsLower);
        foreach ($extra as $item) {
            $slug = Str::slug($item['name']);
            if ($slug !== '' && ! isset($seen[$slug])) {
                $seen[$slug] = true;
                $out[] = $item;
            }
        }

        return array_values($out);
    }

    /**
     * Retourne les N mots les plus fréquents dans les articles de la catégorie,
     * en excluant les termes déjà dans la description et les mots vides.
     *
     * @param  array<string>  $excludeTerms  Termes à exclure (déjà dans la description)
     * @return array<int, array{name: string, slug: string}>
     */
    private function getTopWordsFromArticles(int $limit, array $excludeTerms = []): array
    {
        $articles = Article::query()
            ->where('category_id', $this->id)
            ->where('status', 'published')
            ->whereNotNull('published_at')
            ->select(['title', 'excerpt', 'content', 'keywords', 'meta_title', 'meta_description'])
            ->limit(200)
            ->get();

        $excludeLower = array_map('mb_strtolower', $excludeTerms);
        $stopWords = [
            'le', 'la', 'les', 'un', 'une', 'des', 'et', 'ou', 'mais', 'que', 'qui', 'dont', 'où',
            'ce', 'cette', 'ces', 'son', 'sa', 'ses', 'leur', 'leurs', 'notre', 'nos', 'votre', 'vos',
            'de', 'du', 'au', 'aux', 'en', 'pour', 'par', 'avec', 'sans', 'sous', 'sur', 'dans',
            'est', 'sont', 'été', 'être', 'avoir', 'a', 'ont', 'fait', 'font', 'plus', 'moins',
            'vous', 'nous', 'ils', 'elles', 'lui', 'eux', 'cela', 'celui', 'ceux', 'tous', 'toutes',
            'pas', 'très', 'bien', 'tout', 'toute', 'autre', 'autres', 'même', 'mêmes', 'aussi',
            'peut', 'peuvent', 'doit', 'doivent', 'personnes', 'chose', 'choses', 'année', 'années',
            'een', 'het', 'van', 'een', 'dat', 'die', 'niet', 'zijn', 'was', 'werd', 'worden',
            'the', 'a', 'an', 'and', 'or', 'but', 'in', 'on', 'at', 'to', 'for', 'of', 'with',
            'eacute', 'rsquo', 'nbsp', 'ldquo', 'rdquo', 'hellip', 'agrave', 'egrave', 'ccedil',
        ];

        $counts = [];
        foreach ($articles as $article) {
            $raw = implode(' ', array_filter([
                $article->title ?? '',
                $article->excerpt ?? '',
                $article->content ?? '',
                $article->meta_title ?? '',
                $article->meta_description ?? '',
                is_array($article->keywords) ? implode(' ', $article->keywords) : '',
            ]));
            $text = strip_tags($raw);
            $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
            $words = preg_split('/[\s\p{P}\p{S}]+/u', mb_strtolower($text), -1, PREG_SPLIT_NO_EMPTY);
            foreach ($words as $w) {
                $w = trim($w);
                if (mb_strlen($w) < 3) {
                    continue;
                }
                if (in_array($w, $stopWords, true) || in_array($w, $excludeLower, true)) {
                    continue;
                }
                $counts[$w] = ($counts[$w] ?? 0) + 1;
            }
        }

        arsort($counts);
        $top = array_slice(array_keys($counts), 0, $limit);
        $out = [];
        foreach ($top as $word) {
            $out[] = [
                'name' => mb_convert_case($word, MB_CASE_TITLE, 'UTF-8'),
                'slug' => Str::slug($word),
            ];
        }
        return $out;
    }
}
