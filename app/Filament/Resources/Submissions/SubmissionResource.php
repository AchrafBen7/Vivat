<?php

namespace App\Filament\Resources\Submissions;

use App\Filament\Resources\Submissions\Pages\ListSubmissions;
use App\Filament\Resources\Submissions\Pages\ViewSubmission;
use App\Models\Category;
use App\Models\Submission;
use App\Services\SubmissionPublishingService;
use BackedEnum;
use Filament\Actions\Action as TableAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;

class SubmissionResource extends Resource
{
    protected static ?string $model = Submission::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $navigationLabel = 'Soumissions';

    protected static ?string $modelLabel = 'soumission';

    protected static ?string $pluralModelLabel = 'soumissions';

    protected static string|\UnitEnum|null $navigationGroup = 'Editorial';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Placeholder::make('read_only_message')
                    ->label('Modération')
                    ->content('Les soumissions sont gérées via les actions de modération, pas via un formulaire CRUD classique.'),
            ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Soumission')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('title')
                            ->label('Titre')
                            ->weight(FontWeight::SemiBold),
                        TextEntry::make('status')
                            ->label('Statut')
                            ->badge()
                            ->formatStateUsing(fn (string $state): string => match ($state) {
                                'draft' => 'Brouillon',
                                'pending' => 'En attente',
                                'approved' => 'Approuvée',
                                'rejected' => 'Rejetée',
                                default => ucfirst($state),
                            })
                            ->color(fn (string $state): string => match ($state) {
                                'pending' => 'warning',
                                'approved' => 'success',
                                'rejected' => 'danger',
                                default => 'gray',
                            }),
                        TextEntry::make('user.name')
                            ->label('Auteur'),
                        TextEntry::make('category.name')
                            ->label('Catégorie'),
                        TextEntry::make('reading_time')
                            ->label('Temps de lecture')
                            ->formatStateUsing(fn ($state): string => ($state ?: 5) . ' min'),
                        TextEntry::make('created_at')
                            ->label('Soumise le')
                            ->dateTime('d/m/Y H:i'),
                        TextEntry::make('reviewer.name')
                            ->label('Relue par')
                            ->placeholder('Pas encore'),
                        TextEntry::make('reviewed_at')
                            ->label('Relue le')
                            ->dateTime('d/m/Y H:i')
                            ->placeholder('Pas encore'),
                    ]),
                Section::make('Couverture')
                    ->schema([
                        ImageEntry::make('cover_image_path')
                            ->label('')
                            ->height(260),
                    ])
                    ->collapsible(),
                Section::make('Contenu')
                    ->schema([
                        TextEntry::make('excerpt')
                            ->label('Extrait')
                            ->markdown()
                            ->columnSpanFull(),
                        TextEntry::make('content')
                            ->label('Article')
                            ->html()
                            ->formatStateUsing(function (?string $state): string {
                                if (! is_string($state) || trim($state) === '') {
                                    return '<p>Aucun contenu.</p>';
                                }

                                if (preg_match('/<\s*(p|h1|h2|h3|h4|ul|ol|li|blockquote|img|figure|div|section)\b/i', $state)) {
                                    return $state;
                                }

                                $paragraphs = preg_split("/\R{2,}/", trim($state)) ?: [];

                                return collect($paragraphs)
                                    ->map(fn (string $paragraph): string => '<p>' . nl2br(e(trim($paragraph))) . '</p>')
                                    ->implode('');
                            })
                            ->columnSpanFull(),
                        TextEntry::make('reviewer_notes')
                            ->label('Notes admin')
                            ->placeholder('Aucune note')
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->with(['user', 'category', 'reviewer'])->orderByRaw("FIELD(status, 'pending', 'draft', 'approved', 'rejected')")->orderByDesc('created_at'))
            ->columns([
                ImageColumn::make('cover_image_path')
                    ->label('')
                    ->circular(),
                TextColumn::make('title')
                    ->label('Titre')
                    ->searchable()
                    ->wrap()
                    ->weight(FontWeight::SemiBold),
                TextColumn::make('user.name')
                    ->label('Auteur')
                    ->searchable(),
                TextColumn::make('category.name')
                    ->label('Catégorie')
                    ->toggleable(),
                TextColumn::make('status')
                    ->label('Statut')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'draft' => 'Brouillon',
                        'pending' => 'En attente',
                        'approved' => 'Approuvée',
                        'rejected' => 'Rejetée',
                        default => ucfirst($state),
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'approved' => 'success',
                        'rejected' => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('reading_time')
                    ->label('Lecture')
                    ->formatStateUsing(fn ($state): string => ($state ?: 5) . ' min')
                    ->alignCenter(),
                TextColumn::make('created_at')
                    ->label('Soumise le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'pending' => 'En attente',
                        'draft' => 'Brouillon',
                        'approved' => 'Approuvée',
                        'rejected' => 'Rejetée',
                    ])
                    ->default('pending'),
            ])
            ->recordActions([
                ViewAction::make()
                    ->label('Aperçu')
                    ->url(fn (Submission $record): string => route('contributor.articles.show', ['submission' => $record->slug]))
                    ->openUrlInNewTab(),
                TableAction::make('moderate')
                    ->label('Modérer')
                    ->icon(Heroicon::OutlinedPencilSquare)
                    ->color('gray')
                    ->url(fn (Submission $record): string => static::getUrl('view', ['record' => $record])),
                TableAction::make('approve')
                    ->label('Approuver')
                    ->icon(Heroicon::OutlinedCheckCircle)
                    ->color('success')
                    ->visible(fn (Submission $record): bool => $record->status === 'pending')
                    ->form([
                        Select::make('category_id')
                            ->label('Catégorie de publication')
                            ->options(fn (): array => Category::query()->orderBy('name')->pluck('name', 'id')->all())
                            ->default(fn (Submission $record): ?string => $record->category_id)
                            ->searchable()
                            ->required(),
                        Select::make('article_type')
                            ->label("Type d'article")
                            ->options([
                                'hot_news' => 'Hot news',
                                'standard' => 'Standard',
                                'long_form' => 'Long form',
                            ])
                            ->default('standard')
                            ->required(),
                        Textarea::make('notes')
                            ->label('Notes admin')
                            ->rows(4)
                            ->maxLength(2000),
                    ])
                    ->action(function (Submission $record, array $data): void {
                        app(SubmissionPublishingService::class)->approveAndPublish(
                            submission: $record,
                            data: $data,
                            reviewer: auth()->user(),
                        );
                    })
                    ->successNotificationTitle('Soumission approuvée et article publié.'),
                TableAction::make('reject')
                    ->label('Rejeter')
                    ->icon(Heroicon::OutlinedXCircle)
                    ->color('danger')
                    ->visible(fn (Submission $record): bool => $record->status === 'pending')
                    ->form([
                        Textarea::make('notes')
                            ->label('Motif du rejet')
                            ->rows(4)
                            ->required()
                            ->maxLength(2000),
                    ])
                    ->action(function (Submission $record, array $data): void {
                        app(SubmissionPublishingService::class)->reject(
                            submission: $record,
                            data: $data,
                            reviewer: auth()->user(),
                        );
                    })
                    ->successNotificationTitle('Soumission rejetée.'),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSubmissions::route('/'),
            'view' => ViewSubmission::route('/{record}'),
        ];
    }
}
