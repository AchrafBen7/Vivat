<?php

namespace App\Filament\Resources\Submissions;

use App\Filament\Resources\Submissions\Pages\ListSubmissions;
use App\Filament\Resources\Submissions\Pages\ViewSubmission;
use App\Models\Category;
use App\Models\Submission;
use App\Services\PaymentRefundService;
use App\Services\SubmissionPublishingService;
use BackedEnum;
use Filament\Actions\Action as TableAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
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
use Illuminate\Support\HtmlString;

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
                Section::make('Paiement')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('payment.status')
                            ->label('Statut de paiement')
                            ->badge()
                            ->placeholder('Aucun paiement')
                            ->formatStateUsing(fn (?string $state): string => match ($state) {
                                'pending' => 'En attente',
                                'paid' => 'Payé',
                                'refunded' => 'Remboursé',
                                'failed' => 'Échoué',
                                null => 'Aucun paiement',
                                default => ucfirst($state),
                            })
                            ->color(fn (?string $state): string => match ($state) {
                                'pending' => 'warning',
                                'paid' => 'success',
                                'refunded' => 'gray',
                                'failed' => 'danger',
                                default => 'gray',
                            }),
                        TextEntry::make('payment.amount')
                            ->label('Montant')
                            ->placeholder('Aucun paiement')
                            ->formatStateUsing(fn (?int $state, Submission $record): string => $state
                                ? number_format($state / 100, 2, ',', ' ') . ' ' . strtoupper($record->payment?->currency ?? 'EUR')
                                : 'Aucun paiement'),
                        TextEntry::make('payment.created_at')
                            ->label('Payé le')
                            ->dateTime('d/m/Y H:i')
                            ->placeholder('Pas encore'),
                        TextEntry::make('payment.refund_reason')
                            ->label('Motif du remboursement')
                            ->placeholder('Aucun remboursement')
                            ->columnSpanFull(),
                    ])
                    ->collapsible(),
                Section::make('Couverture')
                    ->schema([
                        ImageEntry::make('cover_image_url')
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
            ->modifyQueryUsing(fn (Builder $query) => $query->with(['user', 'category', 'reviewer', 'payment'])->orderByRaw("FIELD(status, 'pending', 'draft', 'approved', 'rejected')")->orderByDesc('created_at'))
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
                    ->label('Soumission')
                    ->searchable()
                    ->wrap()
                    ->weight(FontWeight::SemiBold)
                    ->lineClamp(2)
                    ->description(function (Submission $record): HtmlString {
                        $author = e($record->user?->name ?? 'Auteur inconnu');
                        $category = e($record->category?->name ?? 'Sans catégorie');
                        $readingTime = (int) ($record->reading_time ?: 5);
                        $createdAt = e($record->created_at?->format('d/m/Y à H:i') ?? 'Date inconnue');
                        $excerpt = e((string) str($record->excerpt ?: $record->content ?: 'Aucun extrait disponible.')
                            ->stripTags()
                            ->squish()
                            ->limit(130));

                        return new HtmlString(
                            '<div class="mt-1 space-y-1 text-xs text-gray-500">'
                            . '<div><span class="font-medium text-gray-700">' . $author . '</span> · ' . $category . ' · ' . $readingTime . ' min</div>'
                            . '<div>' . $createdAt . '</div>'
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
                    })
                    ->icon(fn (string $state): Heroicon => match ($state) {
                        'pending' => Heroicon::OutlinedClock,
                        'approved' => Heroicon::OutlinedCheckCircle,
                        'rejected' => Heroicon::OutlinedXCircle,
                        default => Heroicon::OutlinedDocumentText,
                    })
                    ->description(function (Submission $record): ?string {
                        if (! $record->reviewed_at && ! $record->reviewer) {
                            return null;
                        }

                        $parts = [];

                        if ($record->reviewer?->name) {
                            $parts[] = 'Par ' . $record->reviewer->name;
                        }

                        if ($record->reviewed_at) {
                            $parts[] = $record->reviewed_at->format('d/m/Y H:i');
                        }

                        return implode(' · ', $parts);
                    }),
                TextColumn::make('payment.status')
                    ->label('Paiement')
                    ->badge()
                    ->alignCenter()
                    ->placeholder('Aucun')
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'pending' => 'En attente',
                        'paid' => 'Payé',
                        'refunded' => 'Remboursé',
                        'failed' => 'Échoué',
                        null => 'Aucun',
                        default => ucfirst($state),
                    })
                    ->color(fn (?string $state): string => match ($state) {
                        'pending' => 'warning',
                        'paid' => 'success',
                        'refunded' => 'gray',
                        'failed' => 'danger',
                        default => 'gray',
                    })
                    ->description(fn (Submission $record): ?string => $record->payment
                        ? number_format($record->payment->amount / 100, 2, ',', ' ') . ' ' . strtoupper($record->payment->currency)
                        : null),
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
            ->paginated([10, 25, 50])
            ->defaultPaginationPageOption(10)
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
                            ->helperText('Choisissez la rubrique dans laquelle l’article sera publié.')
                            ->required(),
                        Select::make('article_type')
                            ->label("Type d'article")
                            ->options([
                                'hot_news' => 'Hot news',
                                'standard' => 'Standard',
                                'long_form' => 'Long form',
                            ])
                            ->default('standard')
                            ->helperText('Ce choix influence la mise en avant visuelle de l’article sur le site.')
                            ->required(),
                        Textarea::make('notes')
                            ->label('Notes admin')
                            ->rows(4)
                            ->helperText('Optionnel. Ce message peut servir de contexte éditorial ou de suivi interne.')
                            ->maxLength(2000),
                    ])
                    ->action(function (Submission $record, array $data): void {
                        app(SubmissionPublishingService::class)->approveAndPublish(
                            submission: $record,
                            data: $data,
                            reviewer: auth()->user(),
                        );
                    })
                    ->successNotification(
                        fn (Submission $record) => Notification::make()
                            ->success()
                            ->title('Article publié')
                            ->body('La soumission "' . $record->title . '" a été approuvée et est maintenant visible sur le site.')
                    ),
                TableAction::make('reject')
                    ->label('Rejeter')
                    ->icon(Heroicon::OutlinedXCircle)
                    ->color('danger')
                    ->visible(fn (Submission $record): bool => $record->status === 'pending')
                    ->form([
                        Textarea::make('notes')
                            ->label('Motif du rejet')
                            ->rows(4)
                            ->helperText('Expliquez clairement ce qui doit être corrigé avant un nouvel envoi.')
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
                    ->successNotification(
                        fn (Submission $record) => Notification::make()
                            ->success()
                            ->title('Soumission rejetée')
                            ->body('Le retour éditorial a été enregistré pour "' . $record->title . '". Le rédacteur peut maintenant corriger puis renvoyer son article.')
                    ),
                TableAction::make('refund')
                    ->label('Rembourser')
                    ->icon(Heroicon::OutlinedArrowUturnLeft)
                    ->color('danger')
                    ->visible(fn (Submission $record): bool => (bool) $record->payment?->isRefundable())
                    ->form([
                        Textarea::make('reason')
                            ->label('Motif du remboursement')
                            ->rows(4)
                            ->default('Article refusé')
                            ->helperText('Ce message sera aussi visible dans le mail envoyé au rédacteur.')
                            ->maxLength(255),
                    ])
                    ->requiresConfirmation()
                    ->modalHeading('Rembourser ce paiement ?')
                    ->modalDescription('Le montant Stripe sera remboursé et le rédacteur recevra un email de confirmation.')
                    ->action(function (Submission $record, array $data): void {
                        app(PaymentRefundService::class)->refund(
                            $record->payment,
                            $data['reason'] ?? 'Article refusé',
                        );

                        $record->refresh();
                    })
                    ->successNotification(
                        fn (Submission $record) => Notification::make()
                            ->success()
                            ->title('Paiement remboursé')
                            ->body('Le remboursement de "' . $record->title . '" a été traité et le rédacteur a été notifié.')
                    ),
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
