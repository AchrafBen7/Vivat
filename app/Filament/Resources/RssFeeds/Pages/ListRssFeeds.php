<?php

namespace App\Filament\Resources\RssFeeds\Pages;

use App\Filament\Resources\RssFeeds\RssFeedResource;
use App\Jobs\FetchRssFeedJob;
use App\Models\RssFeed;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Icons\Heroicon;

class ListRssFeeds extends ListRecords
{
    protected static string $resource = RssFeedResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('fetchDueFeeds')
                ->label('Fetcher les flux dus')
                ->icon(Heroicon::OutlinedArrowPath)
                ->color('primary')
                ->action(function (): void {
                    $feeds = RssFeed::dueForFetch()->get();

                    foreach ($feeds as $feed) {
                        FetchRssFeedJob::dispatch($feed);
                    }

                    Notification::make()
                        ->success()
                        ->title('Fetch lancé')
                        ->body($feeds->isEmpty()
                            ? "Aucun flux n'était à rafraîchir."
                            : $feeds->count() . ' flux ont été ajoutés à la queue RSS.')
                        ->send();
                }),
        ];
    }
}
