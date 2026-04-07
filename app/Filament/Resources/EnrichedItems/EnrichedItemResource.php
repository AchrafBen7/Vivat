<?php

namespace App\Filament\Resources\EnrichedItems;

use App\Filament\Resources\EnrichedItems\Pages\ListEnrichedItems;
use App\Jobs\GenerateArticleJob;
use App\Models\Category;
use App\Models\EnrichedItem;
use BackedEnum;
use Filament\Actions\Action as TableAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\HtmlString;

class EnrichedItemResource extends Resource
{
    protected static ?string $model = EnrichedItem::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCpuChip;

    protected static ?string $navigationLabel = 'Analyses IA';

    protected static ?string $modelLabel = 'analyse IA';

    protected static ?string $pluralModelLabel = 'analyses IA';

    protected static string|\UnitEnum|null $navigationGroup = 'Assistant IA';

    protected static ?int $navigationSort = 4;

    protected static bool $shouldRegisterNavigation = false;

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query
                ->with(['rssItem.rssFeed.source', 'rssItem.category'])
                ->orderByDesc('enriched_at'))
            ->columns([
                TextColumn::make('rssItem.title')
                    ->label('Contenu enrichi')
                    ->searchable()
                    ->wrap()
                    ->weight(FontWeight::SemiBold)
                    ->lineClamp(2)
                    ->description(function (EnrichedItem $record): HtmlString {
                        $source = e($record->rssItem?->rssFeed?->source?->name ?? 'Source inconnue');
                        $category = e($record->rssItem?->category?->name ?? 'Sans catégorie');
                        $topic = e($record->primary_topic ?: 'Sujet non détecté');
                        $lead = e((string) str($record->lead ?: 'Aucun lead.')
                            ->stripTags()
                            ->squish()
                            ->limit(120));

                        return new HtmlString(
                            '<div class="mt-1 space-y-1 text-xs text-gray-500">'
                            . '<div><span class="font-medium text-gray-700">' . $source . '</span> · ' . $category . '</div>'
                            . '<div>Sujet IA · ' . $topic . '</div>'
                            . '<div class="text-gray-600">' . $lead . '</div>'
                            . '</div>'
                        );
                    })
                    ->html(),
                TextColumn::make('quality_score')
                    ->label('Qualité')
                    ->badge()
                    ->color(fn (int $state): string => $state >= 75 ? 'success' : ($state >= 55 ? 'warning' : 'danger')),
                TextColumn::make('seo_score')
                    ->label('SEO')
                    ->badge()
                    ->color(fn (int $state): string => $state >= 75 ? 'success' : ($state >= 55 ? 'warning' : 'danger')),
                TextColumn::make('enriched_at')
                    ->label('Enrichi')
                    ->since(),
            ])
            ->filters([
                SelectFilter::make('rssItem.status')
                    ->label('Statut RSS')
                    ->options([
                        'enriched' => 'Enrichi',
                        'used' => 'Utilisé',
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        if (blank($data['value'] ?? null)) {
                            return $query;
                        }

                        return $query->whereHas('rssItem', fn (Builder $rssQuery) => $rssQuery->where('status', $data['value']));
                    }),
            ])
            ->recordActions([
                TableAction::make('generate')
                    ->label('Générer un brouillon')
                    ->icon(Heroicon::OutlinedSparkles)
                    ->color('primary')
                    ->form([
                        Select::make('category_id')
                            ->label('Catégorie cible')
                            ->options(fn (): array => Category::query()->orderBy('name')->pluck('name', 'id')->all())
                            ->searchable()
                            ->helperText('Laisse vide pour conserver la catégorie détectée.'),
                        Select::make('article_type')
                            ->label("Type d'article")
                            ->options([
                                'hot_news' => 'Hot news',
                                'standard' => 'Standard',
                                'long_form' => 'Long form',
                            ])
                            ->default('standard')
                            ->required(),
                        Textarea::make('custom_prompt')
                            ->label('Instruction supplémentaire')
                            ->rows(3)
                            ->placeholder('Ex: angle plus pédagogique, ton plus synthétique...'),
                    ])
                    ->action(function (EnrichedItem $record, array $data): void {
                        GenerateArticleJob::dispatch(
                            [$record->rss_item_id],
                            $data['category_id'] ?? null,
                            $data['custom_prompt'] ?? null,
                            $data['article_type'] ?? 'standard',
                        );
                    })
                    ->successNotification(
                        fn () => Notification::make()
                            ->success()
                            ->title('Génération lancée')
                            ->body('Un brouillon IA va être généré dans la queue generation.')
                    ),
                TableAction::make('sourceUrl')
                    ->label('Source')
                    ->icon(Heroicon::OutlinedArrowTopRightOnSquare)
                    ->url(fn (EnrichedItem $record): string => $record->rssItem?->url ?? '#')
                    ->openUrlInNewTab(),
            ])
            ->defaultSort('enriched_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListEnrichedItems::route('/'),
        ];
    }
}
