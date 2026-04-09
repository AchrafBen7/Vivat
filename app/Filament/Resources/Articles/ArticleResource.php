<?php

namespace App\Filament\Resources\Articles;

use App\Filament\Resources\Articles\Pages\EditArticle;
use App\Filament\Resources\Articles\Pages\ListArticles;
use App\Models\Article;
use BackedEnum;
use Filament\Actions\Action as TableAction;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
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

    protected static bool $shouldRegisterNavigation = false;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Visuel de couverture')
                    ->description("Vérifie ici si l'image affichée vient bien de l'IA ou d'une image manuelle.")
                    ->columns(3)
                    ->schema([
                        Placeholder::make('cover_preview')
                            ->label('Aperçu')
                            ->content(fn (?Article $record): HtmlString => new HtmlString(self::coverPreviewHtml($record)))
                            ->columnSpan(2),
                        Placeholder::make('cover_status')
                            ->label('Statut de la cover')
                            ->content(fn (?Article $record): HtmlString => new HtmlString(self::coverStatusHtml($record))),
                        TextInput::make('cover_image_url')
                            ->label("URL d'image")
                            ->url()
                            ->maxLength(2048)
                            ->helperText("Laisse vide pour forcer l'utilisation du fallback visuel tant qu'aucune image IA n'a été régénérée.")
                            ->columnSpanFull(),
                    ]),
                Section::make('Paramètres éditoriaux')
                    ->columns(3)
                    ->schema([
                        TextInput::make('title')
                            ->label('Titre')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),
                        TextInput::make('slug')
                            ->label('Slug')
                            ->required()
                            ->maxLength(255),
                        Select::make('category_id')
                            ->label('Catégorie')
                            ->relationship('category', 'name')
                            ->searchable()
                            ->preload(),
                        Select::make('language')
                            ->label('Langue')
                            ->options([
                                'fr' => 'Français',
                                'nl' => 'Néerlandais',
                            ])
                            ->default('fr')
                            ->required(),
                        Select::make('status')
                            ->label('Statut')
                            ->options([
                                'draft' => 'Brouillon',
                                'review' => 'En revue',
                                'published' => 'Publié',
                                'archived' => 'Dépublié',
                            ])
                            ->required(),
                        Select::make('article_type')
                            ->label("Type d'article")
                            ->options([
                                'hot_news' => 'Hot news',
                                'standard' => 'Standard',
                                'long_form' => 'Long format',
                            ])
                            ->default('standard'),
                    ]),
                Section::make('Texte')
                    ->columns(1)
                    ->schema([
                        Textarea::make('excerpt')
                            ->label('Extrait')
                            ->rows(4)
                            ->maxLength(500)
                            ->columnSpanFull(),
                        Textarea::make('content')
                            ->label('Contenu')
                            ->rows(18)
                            ->required()
                            ->helperText("Le HTML est conservé. Utilise cette zone pour corriger le fond, les intertitres et les sources.")
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function hasAiCover(?Article $record): bool
    {
        $cover = (string) ($record?->cover_image_url ?? '');

        if ($cover === '') {
            return false;
        }

        return str_contains($cover, '/generated-covers/')
            || str_contains($cover, 'vivat/generated-covers');
    }

    public static function coverStatusLabel(?Article $record): string
    {
        $cover = (string) ($record?->cover_image_url ?? '');

        if ($cover === '') {
            return 'Aucune image persistée';
        }

        if (self::hasAiCover($record)) {
            return 'Image IA générée';
        }

        if (str_contains($cover, '/submissions/') || str_contains($cover, 'vivat/submissions')) {
            return 'Image manuelle';
        }

        return 'Image externe';
    }

    private static function coverPreviewHtml(?Article $record): string
    {
        $cover = (string) ($record?->cover_image_url ?? '');

        if ($cover === '') {
            return '<div style="border:1px solid #D6E1DD;border-radius:18px;background:#F7FAF9;padding:18px;color:rgba(0,66,65,.62);font-size:13px;">Aucune image enregistrée. Le site affichera un fallback visuel tant qu\'une nouvelle cover n\'aura pas été générée.</div>';
        }

        $escaped = e($cover);

        return '<div style="display:flex;flex-direction:column;gap:12px;">'
            . '<div style="overflow:hidden;border-radius:18px;border:1px solid #D6E1DD;background:#F7FAF9;">'
            . '<img src="' . $escaped . '" alt="Cover" style="display:block;width:100%;height:280px;object-fit:cover;">'
            . '</div>'
            . '</div>';
    }

    private static function coverStatusHtml(?Article $record): string
    {
        $label = self::coverStatusLabel($record);
        $isAi = self::hasAiCover($record);
        $cover = (string) ($record?->cover_image_url ?? '');

        [$bg, $text, $detail] = match (true) {
            $cover === '' => ['#FEF2F2', '#991B1B', 'Aucune image persistée pour cet article.'],
            $isAi => ['#ECFDF5', '#065F46', "Cette image provient de la génération IA et a été enregistrée sur le serveur."],
            str_contains($cover, '/submissions/'), str_contains($cover, 'vivat/submissions') => ['#EFF6FF', '#1D4ED8', "Cette image vient d'un upload manuel ou d'une soumission."],
            default => ['#F7FAF9', '#004241', "Cette image n'est pas identifiée comme une génération IA du pipeline."],
        };

        return '<div style="display:flex;flex-direction:column;gap:12px;">'
            . '<span style="display:inline-flex;width:max-content;align-items:center;border-radius:999px;padding:8px 12px;background:' . $bg . ';color:' . $text . ';font-size:12px;font-weight:700;">' . e($label) . '</span>'
            . '<p style="margin:0;color:rgba(0,66,65,.65);font-size:13px;line-height:1.55;">' . e($detail) . '</p>'
            . '</div>';
    }

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
                        'archived' => 'Dépublié',
                        default => ucfirst($state),
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'published' => 'success',
                        'review' => 'warning',
                        'archived' => 'gray',
                        default => 'gray',
                    }),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'published' => 'Publié',
                        'draft' => 'Brouillon',
                        'review' => 'En revue',
                        'archived' => 'Dépublié',
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
                    ->url(fn (Article $record): string => $record->status === 'published'
                        ? url('/articles/' . $record->slug)
                        : url('/admin-preview/articles/' . $record->slug))
                    ->openUrlInNewTab(),
                TableAction::make('unpublish')
                    ->label('Dépublier')
                    ->icon(Heroicon::OutlinedArrowDownOnSquare)
                    ->color('gray')
                    ->visible(fn (Article $record): bool => $record->status === 'published')
                    ->requiresConfirmation()
                    ->modalHeading("Dépublier l'article ?")
                    ->modalDescription("L'article sera retiré du site public, mais restera disponible dans le dashboard admin.")
                    ->action(function (Article $record): void {
                        $record->update([
                            'status' => 'archived',
                            'published_at' => null,
                        ]);
                    })
                    ->successNotification(
                        fn () => Notification::make()
                            ->success()
                            ->title('Article dépublié')
                            ->body("L'article a été retiré du site public et conservé dans l'administration.")
                    ),
            ])
            ->defaultSort('published_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListArticles::route('/'),
            'edit' => EditArticle::route('/{record}/edit'),
        ];
    }
}
