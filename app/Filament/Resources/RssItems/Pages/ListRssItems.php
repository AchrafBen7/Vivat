<?php

namespace App\Filament\Resources\RssItems\Pages;

use App\Filament\Resources\RssItems\RssItemResource;
use App\Jobs\EnrichContentJob;
use App\Models\RssItem;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Icons\Heroicon;

class ListRssItems extends ListRecords
{
    protected static string $resource = RssItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('enrichPending')
                ->label('Enrichir les nouveaux items')
                ->icon(Heroicon::OutlinedSparkles)
                ->color('primary')
                ->action(function (): void {
                    $items = RssItem::new()->limit(50)->get();

                    foreach ($items as $index => $item) {
                        EnrichContentJob::dispatch($item)
                            ->onQueue('enrichment')
                            ->delay(now()->addSeconds($index * 3));
                    }

                    Notification::make()
                        ->success()
                        ->title('Enrichissement lancé')
                        ->body($items->isEmpty()
                            ? 'Aucun item nouveau à enrichir.'
                            : $items->count() . ' items ont été ajoutés à la queue IA.')
                        ->send();
                }),
        ];
    }
}
