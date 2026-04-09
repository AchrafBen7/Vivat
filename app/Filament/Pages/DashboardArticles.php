<?php

namespace App\Filament\Pages;

use App\Models\Article;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;

class DashboardArticles extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedNewspaper;

    protected static string|\UnitEnum|null $navigationGroup = 'Editorial';

    protected static ?int $navigationSort = 2;

    protected static ?string $navigationLabel = 'Articles';

    protected static ?string $title = 'Articles';

    protected string $view = 'filament.pages.dashboard-articles';

    public string $search = '';

    public string $status = 'published';

    public function getStats(): array
    {
        return [
            'drafts' => Article::where('status', 'draft')->count(),
            'published' => Article::where('status', 'published')->count(),
            'review' => Article::where('status', 'review')->count(),
            'today' => Article::whereDate('created_at', today())->count(),
        ];
    }

    public function getArticles(): array
    {
        return Article::query()
            ->with('category')
            ->when($this->status !== '', fn ($query) => $query->where('status', $this->status))
            ->when($this->search !== '', function ($query) {
                $search = trim($this->search);
                $query->where(function ($subQuery) use ($search): void {
                    $subQuery
                        ->where('title', 'like', '%' . $search . '%')
                        ->orWhere('excerpt', 'like', '%' . $search . '%')
                        ->orWhere('slug', 'like', '%' . $search . '%');
                });
            })
            ->orderByDesc('published_at')
            ->orderByDesc('created_at')
            ->limit(24)
            ->get()
            ->map(function (Article $article): array {
                return [
                    'id' => $article->id,
                    'title' => $article->title,
                    'excerpt' => (string) str($article->excerpt ?: $article->content ?: 'Aucun extrait disponible.')
                        ->stripTags()
                        ->squish()
                        ->limit(160),
                    'status' => $article->status,
                    'category' => $article->category?->name ?? 'Sans catégorie',
                    'type' => match ($article->article_type) {
                        'hot_news' => 'Hot news',
                        'long_form' => 'Long form',
                        default => 'Standard',
                    },
                    'reading_time' => (int) ($article->reading_time ?: 5),
                    'cover' => $article->cover_image_url,
                    'created_at' => $article->created_at?->diffForHumans() ?? 'Date inconnue',
                    'published_at' => $article->published_at?->format('d/m/Y à H:i') ?? 'Non publié',
                    'preview_url' => $article->status === 'published'
                        ? url('/articles/' . $article->slug)
                        : url('/admin-preview/articles/' . $article->slug),
                    'edit_url' => \App\Filament\Resources\Articles\ArticleResource::getUrl('edit', ['record' => $article]),
                ];
            })
            ->toArray();
    }

    public function unpublish(string $articleId): void
    {
        $article = Article::query()->find($articleId);

        if (! $article) {
            Notification::make()
                ->danger()
                ->title('Article introuvable')
                ->send();

            return;
        }

        $article->update([
            'status' => 'archived',
            'published_at' => null,
        ]);

        Notification::make()
            ->success()
            ->title('Article dépublié')
            ->body("L'article a été retiré du site public.")
            ->send();
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('refresh')
                ->label('Rafraîchir')
                ->icon(Heroicon::OutlinedArrowPath)
                ->color('gray')
                ->action(function (): void {
                    Notification::make()
                        ->success()
                        ->title('Liste actualisée')
                        ->send();
                }),
        ];
    }
}
