<?php

namespace App\Filament\Resources\RssFeeds;

use App\Filament\Resources\RssFeeds\Pages\ListRssFeeds;
use App\Jobs\FetchRssFeedJob;
use App\Models\RssFeed;
use BackedEnum;
use Filament\Actions\Action as TableAction;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\HtmlString;

class RssFeedResource extends Resource
{
    protected static ?string $model = RssFeed::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRss;

    protected static ?string $navigationLabel = 'Flux RSS';

    protected static ?string $modelLabel = 'flux RSS';

    protected static ?string $pluralModelLabel = 'flux RSS';

    protected static string|\UnitEnum|null $navigationGroup = 'Pipeline IA';

    protected static ?int $navigationSort = 2;

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query
                ->with(['source', 'category'])
                ->withCount('items')
                ->orderByDesc('is_active')
                ->orderBy('last_fetched_at'))
            ->columns([
                TextColumn::make('feed_url')
                    ->label('Flux')
                    ->searchable()
                    ->wrap()
                    ->weight(FontWeight::SemiBold)
                    ->description(function (RssFeed $record): HtmlString {
                        $source = e($record->source?->name ?? 'Source inconnue');
                        $category = e($record->category?->name ?? 'Sans catégorie');
                        $interval = (int) ($record->fetch_interval_minutes ?: 60);

                        return new HtmlString(
                            '<div class="mt-1 space-y-1 text-xs text-gray-500">'
                            . '<div><span class="font-medium text-gray-700">' . $source . '</span> · ' . $category . '</div>'
                            . '<div>Rafraîchissement toutes les ' . $interval . ' min</div>'
                            . '</div>'
                        );
                    })
                    ->html(),
                TextColumn::make('is_active')
                    ->label('Statut')
                    ->badge()
                    ->formatStateUsing(fn (bool $state): string => $state ? 'Actif' : 'Inactif')
                    ->color(fn (bool $state): string => $state ? 'success' : 'gray'),
                TextColumn::make('items_count')
                    ->label('Items')
                    ->alignCenter(),
                TextColumn::make('last_fetched_at')
                    ->label('Dernier fetch')
                    ->since()
                    ->placeholder('Jamais'),
            ])
            ->filters([
                SelectFilter::make('is_active')
                    ->label('Statut')
                    ->options([
                        '1' => 'Actif',
                        '0' => 'Inactif',
                    ]),
            ])
            ->recordActions([
                TableAction::make('fetch')
                    ->label('Lancer le fetch')
                    ->icon(Heroicon::OutlinedArrowPath)
                    ->color('primary')
                    ->action(function (RssFeed $record): void {
                        FetchRssFeedJob::dispatch($record);
                    })
                    ->successNotification(
                        fn () => Notification::make()
                            ->success()
                            ->title('Fetch dispatché')
                            ->body('Le flux RSS a été ajouté à la queue de scraping.')
                    ),
                TableAction::make('open')
                    ->label('Ouvrir')
                    ->icon(Heroicon::OutlinedArrowTopRightOnSquare)
                    ->url(fn (RssFeed $record): string => $record->feed_url)
                    ->openUrlInNewTab(),
            ])
            ->defaultSort('last_fetched_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListRssFeeds::route('/'),
        ];
    }
}
