<?php

namespace App\Filament\Widgets;

use App\Models\Article;
use App\Models\Submission;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class EditorialOverview extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $pendingSubmissions = Submission::query()->where('status', 'pending')->count();
        $approvedToday = Submission::query()
            ->where('status', 'approved')
            ->whereDate('reviewed_at', today())
            ->count();
        $publishedArticles = Article::query()
            ->where('status', 'published')
            ->whereNotNull('published_at')
            ->count();

        return [
            Stat::make('Soumissions en attente', (string) $pendingSubmissions)
                ->description('Articles a relire et moderer')
                ->color($pendingSubmissions > 0 ? 'warning' : 'success'),
            Stat::make("Approuvees aujourd'hui", (string) $approvedToday)
                ->description('Soumissions converties en articles')
                ->color('success'),
            Stat::make('Articles publies', (string) $publishedArticles)
                ->description('Contenu actuellement visible sur le site')
                ->color('primary'),
        ];
    }
}
