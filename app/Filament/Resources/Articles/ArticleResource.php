<?php

namespace App\Filament\Resources\Articles;

use App\Filament\Resources\Articles\Pages\ListArticles;
use App\Models\Article;
use BackedEnum;
use Filament\Actions\Action as TableAction;
use Filament\Actions\DeleteAction;
use Filament\Resources\Resource;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\HtmlString;

class ArticleResource extends Resource
{
    protected static ?string $model = Article::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedNewspaper;

    protected static ?string $navigationLabel = 'Articles';

    protected static ?string $modelLabel = 'article';

    protected static ?string $pluralModelLabel = 'articles';

    protected static string|\UnitEnum|null $navigationGroup = 'Editorial';

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->with('category')->orderByDesc('published_at')->orderByDesc('created_at'))
            ->columns([
                ImageColumn::make('cover_image_url')
                    ->label('')
                    ->square()
                    ->size(86)
                    ->defaultImageUrl(url('/technologie.jpg'))
                    ->extraImgAttributes([
                        'class' => 'rounded-2xl object-cover shadow-sm',
                    ]),
                TextColumn::make('title')
                    ->label('Article')
                    ->searchable()
                    ->wrap()
                    ->weight(FontWeight::SemiBold)
                    ->lineClamp(2)
                    ->description(function (Article $record): HtmlString {
                        $category = e($record->category?->name ?? 'Sans catégorie');
                        $type = e(match ($record->article_type) {
                            'hot_news' => 'Hot news',
                            'long_form' => 'Long form',
                            default => 'Standard',
                        });
                        $readingTime = (int) ($record->reading_time ?: 5);
                        $publishedAt = e($record->published_at?->format('d/m/Y à H:i') ?? 'Non publié');
                        $excerpt = e((string) str($record->excerpt ?: $record->content ?: 'Aucun extrait disponible.')
                            ->stripTags()
                            ->squish()
                            ->limit(130));

                        return new HtmlString(
                            '<div class="mt-1 space-y-1 text-xs text-gray-500">'
                            . '<div><span class="font-medium text-gray-700">' . $category . '</span> · ' . $type . ' · ' . $readingTime . ' min</div>'
                            . '<div>' . $publishedAt . '</div>'
                            . '<div class="text-gray-600">' . $excerpt . '</div>'
                            . '</div>'
                        );
                    })
                    ->html(),
                TextColumn::make('status')
                    ->label('Statut')
                    ->badge()
                    ->alignCenter()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'published' => 'Publié',
                        'draft' => 'Brouillon',
                        'review' => 'En revue',
                        default => ucfirst($state),
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'published' => 'success',
                        'review' => 'warning',
                        default => 'gray',
                    }),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'published' => 'Publié',
                        'draft' => 'Brouillon',
                        'review' => 'En revue',
                    ])
                    ->default('published'),
                SelectFilter::make('article_type')
                    ->label("Type d'article")
                    ->options([
                        'hot_news' => 'Hot news',
                        'standard' => 'Standard',
                        'long_form' => 'Long form',
                    ]),
            ])
            ->paginated([10, 25, 50])
            ->defaultPaginationPageOption(10)
            ->recordActions([
                TableAction::make('preview')
                    ->label('Aperçu')
                    ->icon(Heroicon::OutlinedEye)
                    ->url(fn (Article $record): string => url('/articles/' . $record->slug))
                    ->openUrlInNewTab(),
                DeleteAction::make()
                    ->label('Supprimer')
                    ->modalHeading("Supprimer l'article ?")
                    ->modalDescription("Cette action retire définitivement l’article du site.")
                    ->successNotificationTitle('Article supprimé.'),
            ])
            ->defaultSort('published_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListArticles::route('/'),
        ];
    }
}
