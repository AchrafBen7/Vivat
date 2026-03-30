<?php

namespace App\Filament\Resources\Clusters;

use App\Filament\Resources\Clusters\Pages\ListClusters;
use App\Models\Cluster;
use BackedEnum;
use Filament\Actions\Action as TableAction;
use Filament\Resources\Resource;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\HtmlString;

class ClusterResource extends Resource
{
    protected static ?string $model = Cluster::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedQueueList;

    protected static ?string $navigationLabel = 'Clusters';

    protected static ?string $modelLabel = 'cluster';

    protected static ?string $pluralModelLabel = 'clusters';

    protected static string|\UnitEnum|null $navigationGroup = 'Pipeline IA';

    protected static ?int $navigationSort = 5;

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query
                ->with(['category', 'article'])
                ->withCount('clusterItems')
                ->orderByDesc('created_at'))
            ->columns([
                TextColumn::make('label')
                    ->label('Cluster')
                    ->searchable()
                    ->wrap()
                    ->weight(FontWeight::SemiBold)
                    ->description(function (Cluster $record): HtmlString {
                        $category = e($record->category?->name ?? 'Sans catégorie');
                        $keywords = collect($record->keywords ?? [])
                            ->take(5)
                            ->map(fn ($keyword) => e((string) $keyword))
                            ->implode(', ');
                        $keywords = $keywords !== '' ? $keywords : 'Aucun mot-clé';

                        return new HtmlString(
                            '<div class="mt-1 space-y-1 text-xs text-gray-500">'
                            . '<div><span class="font-medium text-gray-700">' . $category . '</span> · ' . $record->cluster_items_count . ' items</div>'
                            . '<div>' . $keywords . '</div>'
                            . '</div>'
                        );
                    })
                    ->html(),
                TextColumn::make('status')
                    ->label('Statut')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pending' => 'En attente',
                        'processing' => 'En cours',
                        'generated' => 'Généré',
                        'failed' => 'Échec',
                        default => ucfirst($state),
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'processing' => 'primary',
                        'generated' => 'success',
                        'failed' => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('article.title')
                    ->label('Article lié')
                    ->placeholder('Aucun article')
                    ->limit(40),
                TextColumn::make('created_at')
                    ->label('Créé')
                    ->since(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'pending' => 'En attente',
                        'processing' => 'En cours',
                        'generated' => 'Généré',
                        'failed' => 'Échec',
                    ]),
            ])
            ->recordActions([
                TableAction::make('openArticle')
                    ->label('Article')
                    ->icon(Heroicon::OutlinedNewspaper)
                    ->visible(fn (Cluster $record): bool => filled($record->article?->slug))
                    ->url(fn (Cluster $record): string => url('/articles/' . $record->article->slug))
                    ->openUrlInNewTab(),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListClusters::route('/'),
        ];
    }
}
