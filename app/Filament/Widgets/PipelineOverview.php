<?php

namespace App\Filament\Widgets;

use App\Models\EnrichedItem;
use App\Models\RssFeed;
use App\Models\RssItem;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class PipelineOverview extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $activeFeeds = RssFeed::active()->count();
        $dueFeeds = RssFeed::dueForFetch()->count();
        $newItems = RssItem::query()->where('status', 'new')->count();
        $failedItems = RssItem::query()->where('status', 'failed')->count();
        $enrichedItems = EnrichedItem::query()->count();

        return [
            Stat::make('Flux actifs', (string) $activeFeeds)
                ->description($dueFeeds . ' à rafraîchir')
                ->color($dueFeeds > 0 ? 'warning' : 'success'),
            Stat::make('Items nouveaux', (string) $newItems)
                ->description('Contenus encore à enrichir')
                ->color($newItems > 0 ? 'warning' : 'success'),
            Stat::make('Items enrichis', (string) $enrichedItems)
                ->description('Pool prêt pour la génération IA')
                ->color('primary'),
            Stat::make('Échecs pipeline', (string) $failedItems)
                ->description('Items à vérifier ou relancer')
                ->color($failedItems > 0 ? 'danger' : 'success'),
        ];
    }
}
