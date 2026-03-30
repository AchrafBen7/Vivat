<?php

namespace App\Filament\Resources\Sources;

use App\Filament\Resources\Sources\Pages\ListSources;
use App\Models\Source;
use BackedEnum;
use Filament\Actions\Action as TableAction;
use Filament\Resources\Resource;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\HtmlString;

class SourceResource extends Resource
{
    protected static ?string $model = Source::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedGlobeAlt;

    protected static ?string $navigationLabel = 'Sources';

    protected static ?string $modelLabel = 'source';

    protected static ?string $pluralModelLabel = 'sources';

    protected static string|\UnitEnum|null $navigationGroup = 'Pipeline IA';

    protected static ?int $navigationSort = 1;

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query
                ->withCount(['rssFeeds', 'articleSources'])
                ->orderByDesc('is_active')
                ->orderBy('name'))
            ->columns([
                TextColumn::make('name')
                    ->label('Source')
                    ->searchable()
                    ->weight(FontWeight::SemiBold)
                    ->description(function (Source $record): HtmlString {
                        $baseUrl = e($record->base_url ?: 'URL non définie');
                        $language = e(strtoupper($record->language ?: 'fr'));

                        return new HtmlString(
                            '<div class="mt-1 space-y-1 text-xs text-gray-500">'
                            . '<div>' . $baseUrl . '</div>'
                            . '<div>Langue · ' . $language . '</div>'
                            . '</div>'
                        );
                    })
                    ->html(),
                TextColumn::make('is_active')
                    ->label('Statut')
                    ->badge()
                    ->formatStateUsing(fn (bool $state): string => $state ? 'Active' : 'Inactive')
                    ->color(fn (bool $state): string => $state ? 'success' : 'gray'),
                TextColumn::make('rss_feeds_count')
                    ->label('Flux')
                    ->alignCenter(),
                TextColumn::make('article_sources_count')
                    ->label('Articles générés')
                    ->alignCenter(),
            ])
            ->recordActions([
                TableAction::make('open')
                    ->label('Site')
                    ->icon(Heroicon::OutlinedArrowTopRightOnSquare)
                    ->visible(fn (Source $record): bool => filled($record->base_url))
                    ->url(fn (Source $record): string => $record->base_url)
                    ->openUrlInNewTab(),
            ])
            ->defaultSort('name');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSources::route('/'),
        ];
    }
}
