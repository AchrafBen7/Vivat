<?php

namespace App\Console\Commands;

use App\Mail\NewsletterWeeklyDigestMail;
use App\Models\Article;
use App\Models\Category;
use App\Models\NewsletterSubscriber;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendWeeklyNewsletterCommand extends Command
{
    protected $signature = 'newsletter:send-digest
                            {--dry-run : Affiche les abonnés et articles sans envoyer}
                            {--limit=500 : Nombre max d\'abonnés à traiter}';

    protected $description = 'Envoie le digest hebdomadaire à tous les abonnés confirmés.';

    public function handle(): int
    {
        $isDryRun = (bool) $this->option('dry-run');
        $limit    = (int) $this->option('limit');

        $subscribers = NewsletterSubscriber::active()
            ->limit($limit)
            ->get();

        if ($subscribers->isEmpty()) {
            $this->warn('Aucun abonné actif trouvé.');
            return self::SUCCESS;
        }

        $this->info(sprintf('%d abonné(s) actif(s) trouvé(s).', $subscribers->count()));

        // Pré-charger les articles fr et nl (7 derniers jours, max 6 par locale)
        $articlesFr = $this->getWeeklyArticles('fr');
        $articlesNl = $this->getWeeklyArticles('nl');

        $this->info(sprintf(
            'Articles sélectionnés : %d (fr) / %d (nl)',
            count($articlesFr),
            count($articlesNl)
        ));

        if ($isDryRun) {
            $this->info('[Dry-run] Aucun email envoyé.');
            return self::SUCCESS;
        }

        $sent   = 0;
        $errors = 0;

        foreach ($subscribers as $subscriber) {
            $locale   = $this->detectLocale($subscriber);
            $articles = $locale === 'nl' ? $articlesNl : $articlesFr;

            if (empty($articles)) {
                continue;
            }

            $unsubscribeUrl = route('newsletter.unsubscribe', [
                'token' => $subscriber->getRawOriginal('unsubscribe_token')
                    ?? NewsletterSubscriber::withoutGlobalScopes()
                        ->where('id', $subscriber->id)
                        ->value('unsubscribe_token'),
            ]);

            try {
                Mail::to($subscriber->email)
                    ->queue(new NewsletterWeeklyDigestMail($articles, $unsubscribeUrl, $locale));
                $sent++;
            } catch (\Throwable $e) {
                $this->warn("Échec pour {$subscriber->email}: {$e->getMessage()}");
                $errors++;
            }
        }

        $this->info(sprintf('Digest envoyé : %d emails mis en queue, %d erreurs.', $sent, $errors));

        return $errors > 0 ? self::FAILURE : self::SUCCESS;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function getWeeklyArticles(string $locale): array
    {
        return Article::published()
            ->forLocale($locale)
            ->with('category')
            ->where('published_at', '>=', now()->subDays(7))
            ->orderByDesc('published_at')
            ->limit(6)
            ->get()
            ->map(fn (Article $a) => [
                'title'              => $a->title,
                'slug'               => $a->slug,
                'excerpt'            => $a->excerpt ?? $a->meta_description ?? '',
                'reading_time'       => $a->reading_time,
                'cover_image_url'    => $this->resolveCover($a),
                'category_name'      => $a->category?->name ?? '',
                'published_at_display' => $a->published_at?->locale('fr')->isoFormat('D MMMM YYYY'),
            ])
            ->all();
    }

    private function resolveCover(Article $article): string
    {
        $cover = $article->cover_image_url ?? '';
        if (is_string($cover)
            && $cover !== ''
            && (str_starts_with($cover, 'http') || str_starts_with($cover, '/uploads/'))
            && stripos($cover, 'picsum') === false
        ) {
            return $cover;
        }
        return '';
    }

    private function detectLocale(NewsletterSubscriber $subscriber): string
    {
        $interests = $subscriber->interests ?? [];
        if (in_array('nl', $interests, true) || in_array('dutch', $interests, true)) {
            return 'nl';
        }
        return 'fr';
    }
}
