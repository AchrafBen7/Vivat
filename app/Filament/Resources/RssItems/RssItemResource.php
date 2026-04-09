<?php

namespace App\Filament\Resources\RssItems;

use App\Filament\Resources\RssItems\Pages\ListRssItems;
use App\Jobs\EnrichContentJob;
use App\Models\RssItem;
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

class RssItemResource extends Resource
{
    protected static ?string $model = RssItem::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentMagnifyingGlass;

    protected static ?string $navigationLabel = 'Articles repérés';

    protected static ?string $modelLabel = 'article repéré';

    protected static ?string $pluralModelLabel = 'articles repérés';

    protected static string|\UnitEnum|null $navigationGroup = 'Assistant IA';

    protected static ?int $navigationSort = 3;

    protected static bool $shouldRegisterNavigation = false;

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query
                ->with(['rssFeed.source', 'category', 'enrichedItem'])
                ->orderByRaw("FIELD(status, 'new', 'enriching', 'enriched', 'used', 'failed')")
                ->orderByDesc('published_at')
                ->orderByDesc('created_at'))
            ->columns([
                TextColumn::make('title')
                    ->label('Article')
                    ->searchable()
                    ->wrap()
                    ->weight(FontWeight::SemiBold)
                    ->lineClamp(2)
                    ->description(function (RssItem $record): HtmlString {
                        $source = e($record->rssFeed?->source?->name ?? 'Source inconnue');
                        $category = e($record->category?->name ?? 'Sans catégorie');
                        $publishedAt = e($record->published_at?->format('d/m/Y H:i') ?? 'Date inconnue');
                        $description = e((string) str($record->description ?: 'Aucune description.')
                            ->stripTags()
                            ->squish()
                            ->limit(140));

                        return new HtmlString(
                            '<div class="mt-1 space-y-1 text-xs text-gray-500">'
                            . '<div><span class="font-medium text-gray-700">' . $source . '</span> · ' . $category . '</div>'
                            . '<div>' . $publishedAt . '</div>'
                            . '<div class="text-gray-600">' . $description . '</div>'
                            . '</div>'
                        );
                    })
                    ->html(),
                TextColumn::make('status')
                    ->label('Statut')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'new' => 'Nouveau',
                        'enriching' => 'Enrichissement',
                        'enriched' => 'Enrichi',
                        'used' => 'Utilisé',
                        'failed' => 'Échec',
                        default => ucfirst($state),
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'new' => 'gray',
                        'enriching' => 'warning',
                        'enriched' => 'success',
                        'used' => 'primary',
                        'failed' => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('enrichedItem.primary_topic')
                    ->label('Sujet IA')
                    ->placeholder('Pas encore enrichi')
                    ->limit(30)
                    ->toggleable(),
                TextColumn::make('fetched_at')
                    ->label('Récupéré')
                    ->since()
                    ->placeholder('Inconnu'),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'new' => 'Nouveau',
                        'enriching' => 'Enrichissement',
                        'enriched' => 'Enrichi',
                        'used' => 'Utilisé',
                        'failed' => 'Échec',
                    ])
                    ->default('new'),
            ])
            ->recordActions([
                TableAction::make('enrich')
                    ->label('Enrichir')
                    ->icon(Heroicon::OutlinedSparkles)
                    ->color('primary')
                    ->visible(fn (RssItem $record): bool => in_array($record->status, ['new', 'failed'], true))
                    ->action(function (RssItem $record): void {
                        EnrichContentJob::dispatch($record);
                    })
                    ->successNotification(
                        fn () => Notification::make()
                            ->success()
                            ->title('Enrichissement lancé')
                            ->body("L'item a été ajouté à la queue d'enrichissement.")
                    ),
                TableAction::make('sourceUrl')
                    ->label('Source')
                    ->icon(Heroicon::OutlinedArrowTopRightOnSquare)
                    ->url(fn (RssItem $record): string => $record->url)
                    ->openUrlInNewTab(),
            ])
            ->defaultSort('published_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListRssItems::route('/'),
        ];
    }
}
