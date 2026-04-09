<?php

namespace App\Filament\Resources\Submissions;

use App\Filament\Resources\Submissions\Pages\ListSubmissions;
use App\Filament\Resources\Submissions\Pages\ViewSubmission;
use App\Models\PricePreset;
use App\Models\Submission;
use App\Services\PublicationQuoteService;
use App\Services\SubmissionWorkflowService;
use BackedEnum;
use Filament\Actions\Action as TableAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
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
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\HtmlString;

class SubmissionResource extends Resource
{
    protected static ?string $model = Submission::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $navigationLabel = 'Soumissions';

    protected static ?string $modelLabel = 'soumission';

    protected static ?string $pluralModelLabel = 'soumissions';

    protected static string|\UnitEnum|null $navigationGroup = 'Editorial';

    protected static bool $shouldRegisterNavigation = false;

    /* ------------------------------------------------------------------ */
    /*  Helpers statuts                                                    */
    /* ------------------------------------------------------------------ */

    private static function statusLabel(string $state): string
    {
        return match ($state) {
            'draft'              => 'Brouillon',
            'pending'            => 'Soumis (ancien)',
            'submitted'          => 'Soumis',
            'under_review'       => 'En revue',
            'changes_requested'  => 'Corrections demandées',
            'rejected'           => 'Rejeté',
            'price_proposed'     => 'Prix proposé',
            'awaiting_payment'   => 'En attente de paiement',
            'payment_pending'    => 'Paiement en cours',
            'payment_succeeded'  => 'Paiement reçu',
            'payment_failed'     => 'Paiement échoué',
            'payment_expired'    => 'Offre expirée',
            'payment_canceled'   => 'Paiement annulé',
            'approved'           => 'Approuvé (ancien)',
            'published'          => 'Publié',
            default              => ucfirst($state),
        };
    }

    private static function statusColor(string $state): string
    {
        return match ($state) {
            'submitted', 'pending'     => 'warning',
            'under_review'             => 'info',
            'changes_requested'        => 'warning',
            'rejected', 'payment_failed', 'payment_expired', 'payment_canceled' => 'danger',
            'price_proposed'           => 'info',
            'awaiting_payment', 'payment_pending' => 'warning',
            'payment_succeeded', 'published', 'approved' => 'success',
            default                    => 'gray',
        };
    }

    private static function statusIcon(string $state): Heroicon
    {
        return match ($state) {
            'submitted', 'pending'    => Heroicon::OutlinedPaperAirplane,
            'under_review'            => Heroicon::OutlinedEye,
            'changes_requested'       => Heroicon::OutlinedPencil,
            'rejected'                => Heroicon::OutlinedXCircle,
            'price_proposed'          => Heroicon::OutlinedCurrencyEuro,
            'awaiting_payment', 'payment_pending' => Heroicon::OutlinedCreditCard,
            'payment_succeeded'       => Heroicon::OutlinedCheckBadge,
            'payment_failed', 'payment_expired', 'payment_canceled' => Heroicon::OutlinedExclamationTriangle,
            'published', 'approved'   => Heroicon::OutlinedCheckCircle,
            default                   => Heroicon::OutlinedDocumentText,
        };
    }

    private static function quoteStatusLabel(?string $state): string
    {
        return match ($state) {
            'pending'  => 'En attente',
            'sent'     => 'Envoyée',
            'accepted' => 'Acceptée',
            'expired'  => 'Expirée',
            'canceled' => 'Annulée',
            default    => '',
        };
    }

    private static function quoteStatusColor(?string $state): string
    {
        return match ($state) {
            'sent' => 'warning',
            'accepted' => 'success',
            'expired', 'canceled' => 'danger',
            default => 'gray',
        };
    }

    private static function paymentStatusLabel(?string $state): string
    {
        return match ($state) {
            'pending' => 'En attente',
            'processing' => 'En cours',
            'succeeded' => 'Payé',
            'failed' => 'Échoué',
            'canceled' => 'Annulé',
            'refunded' => 'Remboursé',
            default => '',
        };
    }

    private static function paymentStatusColor(?string $state): string
    {
        return match ($state) {
            'pending', 'processing' => 'warning',
            'succeeded' => 'success',
            'failed', 'canceled' => 'danger',
            'refunded' => 'gray',
            default => 'gray',
        };
    }

    private static function money(?int $amountCents, string $currency = 'eur'): string
    {
        if ($amountCents === null) {
            return '';
        }

        return number_format($amountCents / 100, 2, ',', ' ') . ' ' . strtoupper($currency);
    }

    private static function articleTypeLabel(?string $state): string
    {
        return match ($state) {
            'hot_news' => 'Hot news',
            'long_form' => 'Long format',
            'standard' => 'Standard',
            default => '',
        };
    }

    /* ------------------------------------------------------------------ */
    /*  Form                                                              */
    /* ------------------------------------------------------------------ */

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Placeholder::make('read_only_message')
                ->label('Modération')
                ->content('Les soumissions sont gérées via les actions de modération, pas via un formulaire CRUD classique.'),
        ]);
    }

    /* ------------------------------------------------------------------ */
    /*  Infolist                                                          */
    /* ------------------------------------------------------------------ */

    public static function infolist(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Soumission')
                ->columns(2)
                ->schema([
                    TextEntry::make('title')->label('Titre')->weight(FontWeight::SemiBold),
                    TextEntry::make('status')
                        ->label('Statut')
                        ->badge()
                        ->formatStateUsing(fn (string $state): string => self::statusLabel($state))
                        ->color(fn (string $state): string => self::statusColor($state)),
                    TextEntry::make('user.name')->label('Auteur'),
                    TextEntry::make('category.name')->label('Catégorie'),
                    TextEntry::make('reading_time')
                        ->label('Temps de lecture')
                        ->formatStateUsing(fn ($state): string => ($state ?: 5) . ' min'),
                    TextEntry::make('submitted_at')->label('Soumis le')->dateTime('d/m/Y H:i')->placeholder(''),
                    TextEntry::make('reviewer.name')->label('Relue par')->placeholder('Pas encore'),
                    TextEntry::make('reviewed_at')->label('Relue le')->dateTime('d/m/Y H:i')->placeholder(''),
                    TextEntry::make('reviewer_notes')->label('Notes admin')->placeholder('Aucune')->columnSpanFull(),
                ]),
            Section::make('Proposition de prix')
                ->columns(2)
                ->schema([
                    TextEntry::make('quote.formatted_amount')
                        ->label('Montant proposé')
                        ->placeholder('Aucune proposition'),
                    TextEntry::make('quote.preset.label')
                        ->label('Tarif sélectionné')
                        ->placeholder('Prix libre'),
                    TextEntry::make('quote.article_type')
                        ->label("Type d'article")
                        ->formatStateUsing(fn (?string $state): string => self::articleTypeLabel($state))
                        ->placeholder(''),
                    TextEntry::make('quote.status')
                        ->label('Statut de la quote')
                        ->badge()
                        ->placeholder('')
                        ->formatStateUsing(fn (?string $state): string => self::quoteStatusLabel($state))
                        ->color(fn (?string $state): string => self::quoteStatusColor($state)),
                    TextEntry::make('quote.proposedBy.name')
                        ->label('Proposé par')
                        ->placeholder(''),
                    TextEntry::make('quote.expires_at')
                        ->label('Expire le')
                        ->dateTime('d/m/Y H:i')
                        ->placeholder(''),
                    TextEntry::make('quote.note_to_author')
                        ->label('Message au rédacteur')
                        ->placeholder('Aucun message')
                        ->columnSpanFull(),
                ])
                ->collapsible(),
            Section::make('Paiement lié')
                ->columns(2)
                ->schema([
                    TextEntry::make('latestSubmissionPayment.formatted_amount')
                        ->label('Montant payé')
                        ->placeholder('Aucun paiement'),
                    TextEntry::make('latestSubmissionPayment.status')
                        ->label('Statut du paiement')
                        ->badge()
                        ->formatStateUsing(fn (?string $state): string => self::paymentStatusLabel($state))
                        ->color(fn (?string $state): string => self::paymentStatusColor($state))
                        ->placeholder(''),
                    TextEntry::make('latestSubmissionPayment.paid_at')
                        ->label('Payé le')
                        ->dateTime('d/m/Y H:i')
                        ->placeholder(''),
                    TextEntry::make('latestSubmissionPayment.failure_message')
                        ->label('Erreur paiement')
                        ->placeholder('Aucune')
                        ->columnSpanFull(),
                ])
                ->collapsible(),
            Section::make('Historique des statuts')
                ->schema([
                    TextEntry::make('status_timeline')
                        ->label('')
                        ->state(function (Submission $record): HtmlString {
                            $logs = $record->statusLogs->sortByDesc('created_at')->take(10);

                            if ($logs->isEmpty()) {
                                return new HtmlString('<div class="text-sm text-gray-500">Aucun historique disponible.</div>');
                            }

                            $html = '<div class="space-y-3">';

                            foreach ($logs as $log) {
                                $from = $log->from_status ? self::statusLabel($log->from_status) : '';
                                $to = self::statusLabel($log->to_status);
                                $by = e($log->triggeredBy?->name ?? match ($log->trigger_source) {
                                    'system' => 'Système',
                                    'stripe_webhook' => 'Stripe',
                                    'author' => 'Rédacteur',
                                    default => 'Admin',
                                });
                                $date = e($log->created_at?->format('d/m/Y à H:i') ?? '');
                                $reason = $log->reason ? '<div class="mt-1 text-xs text-gray-600">' . e($log->reason) . '</div>' : '';

                                $html .= '<div class="rounded-2xl border border-gray-200 bg-gray-50 px-4 py-3">';
                                $html .= '<div class="text-sm font-medium text-gray-900">' . e($from) . ' → ' . e($to) . '</div>';
                                $html .= '<div class="mt-1 text-xs text-gray-500">' . $date . ' · ' . $by . '</div>';
                                $html .= $reason;
                                $html .= '</div>';
                            }

                            $html .= '</div>';

                            return new HtmlString($html);
                        })
                        ->html()
                        ->columnSpanFull(),
                ])
                ->collapsible(),
            Section::make('Couverture')
                ->schema([
                    ImageEntry::make('cover_image_url')->label('')->height(260),
                ])
                ->collapsible(),
            Section::make('Contenu')
                ->schema([
                    TextEntry::make('excerpt')->label('Extrait')->markdown()->columnSpanFull(),
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
                                ->map(fn (string $p): string => '<p>' . nl2br(e(trim($p))) . '</p>')
                                ->implode('');
                        })
                        ->columnSpanFull(),
                ]),
        ]);
    }

    /* ------------------------------------------------------------------ */
    /*  Table                                                             */
    /* ------------------------------------------------------------------ */

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query
                ->with(['user', 'category', 'reviewer', 'quote.preset', 'quote.proposedBy', 'latestSubmissionPayment', 'statusLogs.triggeredBy'])
                ->whereNotIn('status', ['draft'])
                ->orderByRaw("FIELD(status, 'submitted','pending','under_review','changes_requested','price_proposed','awaiting_payment','payment_pending','payment_failed','payment_succeeded','published','rejected','payment_expired','payment_canceled','approved')")
                ->orderByDesc('submitted_at')
                ->orderByDesc('created_at'))
            ->columns([
                ImageColumn::make('cover_image_url')
                    ->label('')
                    ->square()
                    ->size(86)
                    ->defaultImageUrl(url('/technologie.jpg'))
                    ->extraImgAttributes(['class' => 'rounded-2xl object-cover shadow-sm']),
                TextColumn::make('title')
                    ->label('Soumission')
                    ->searchable()
                    ->wrap()
                    ->weight(FontWeight::SemiBold)
                    ->lineClamp(2)
                    ->description(function (Submission $record): HtmlString {
                        $author     = e($record->user?->name ?? 'Auteur inconnu');
                        $category   = e($record->category?->name ?? 'Sans catégorie');
                        $readingTime= (int) ($record->reading_time ?: 5);
                        $date       = e($record->submitted_at?->format('d/m/Y à H:i') ?? $record->created_at?->format('d/m/Y à H:i') ?? '');
                        $excerpt    = e((string) str($record->excerpt ?: $record->content ?: '')
                            ->stripTags()->squish()->limit(120));

                        return new HtmlString(
                            '<div class="mt-1 space-y-1 text-xs text-gray-500">'
                            . '<div><span class="font-medium text-gray-700">' . $author . '</span> · ' . $category . ' · ' . $readingTime . ' min · ' . $date . '</div>'
                            . ($excerpt ? '<div class="text-gray-600">' . $excerpt . '</div>' : '')
                            . '</div>'
                        );
                    })
                    ->html(),
                TextColumn::make('status')
                    ->label('Statut')
                    ->badge()
                    ->alignCenter()
                    ->formatStateUsing(fn (string $state): string => self::statusLabel($state))
                    ->color(fn (string $state): string => self::statusColor($state))
                    ->icon(fn (string $state): Heroicon => self::statusIcon($state))
                    ->description(function (Submission $record): ?string {
                        if ($record->quote) {
                            return $record->quote->formatted_amount . ' · exp. ' . $record->quote->expires_at?->format('d/m');
                        }
                        if ($record->reviewed_at) {
                            return 'Relu le ' . $record->reviewed_at->format('d/m/Y');
                        }
                        return null;
                    }),
                TextColumn::make('quote.amount_cents')
                    ->label('Prix')
                    ->formatStateUsing(fn ($state, Submission $record): string => self::money($record->quote?->amount_cents, $record->quote?->currency ?? 'eur'))
                    ->badge()
                    ->color('gray')
                    ->toggleable(),
                TextColumn::make('latestSubmissionPayment.status')
                    ->label('Paiement')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => self::paymentStatusLabel($state))
                    ->color(fn (?string $state): string => self::paymentStatusColor($state))
                    ->description(fn (Submission $record): ?string => $record->latestSubmissionPayment?->paid_at?->format('d/m/Y à H:i'))
                    ->toggleable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'submitted'         => 'Soumis',
                        'pending'           => 'Soumis (ancien)',
                        'under_review'      => 'En revue',
                        'changes_requested' => 'Corrections demandées',
                        'price_proposed'    => 'Prix proposé',
                        'awaiting_payment'  => 'En attente de paiement',
                        'payment_pending'   => 'Paiement en cours',
                        'payment_succeeded' => 'Paiement reçu',
                        'payment_failed'    => 'Paiement échoué',
                        'payment_expired'   => 'Offre expirée',
                        'published'         => 'Publié',
                        'rejected'          => 'Rejeté',
                    ]),
            ])
            ->paginated([10, 25, 50])
            ->defaultPaginationPageOption(10)
            ->recordActions([
                ViewAction::make()
                    ->label('Aperçu')
                    ->url(fn (Submission $record): string => route('contributor.articles.show', ['submission' => $record->slug]))
                    ->openUrlInNewTab(),

                TableAction::make('moderate')
                    ->label('Ouvrir')
                    ->icon(Heroicon::OutlinedPencilSquare)
                    ->color('gray')
                    ->url(fn (Submission $record): string => static::getUrl('view', ['record' => $record])),

                // ── Démarrer la revue ────────────────────────────────
                TableAction::make('start_review')
                    ->label('Démarrer la revue')
                    ->icon(Heroicon::OutlinedEye)
                    ->color('info')
                    ->visible(fn (Submission $record): bool => in_array($record->status, ['submitted', 'pending'], true))
                    ->action(function (Submission $record): void {
                        app(SubmissionWorkflowService::class)->startReview($record, auth()->user());
                        $record->refresh();
                    })
                    ->successNotification(fn (Submission $r) => Notification::make()->info()->title('Revue démarrée')->body('"' . $r->title . '" est maintenant en cours de revue.')),

                // ── Demander des modifications ───────────────────────
                TableAction::make('request_changes')
                    ->label('Demander des corrections')
                    ->icon(Heroicon::OutlinedPencil)
                    ->color('warning')
                    ->visible(fn (Submission $record): bool => $record->status === 'under_review')
                    ->form([
                        Textarea::make('note')
                            ->label('Message au rédacteur')
                            ->rows(5)
                            ->required()
                            ->maxLength(3000)
                            ->helperText('Expliquez clairement ce qui doit être corrigé avant un nouvel envoi.'),
                    ])
                    ->action(function (Submission $record, array $data): void {
                        app(SubmissionWorkflowService::class)->requestChanges($record, auth()->user(), $data['note']);
                    })
                    ->successNotification(fn (Submission $r) => Notification::make()->warning()->title('Corrections demandées')->body('Le rédacteur a été notifié pour "' . $r->title . '".')),

                // ── Proposer un prix ─────────────────────────────────
                TableAction::make('propose_price')
                    ->label('Proposer un prix')
                    ->icon(Heroicon::OutlinedCurrencyEuro)
                    ->color('success')
                    ->visible(fn (Submission $record): bool => $record->status === 'under_review')
                    ->form(function (): array {
                        $presets = PricePreset::active()->get();

                        return [
                            Radio::make('price_mode')
                                ->label('Type de tarif')
                                ->options([
                                    'preset' => 'Tarif prédéfini',
                                    'custom' => 'Montant libre',
                                ])
                                ->default('preset')
                                ->reactive(),

                            Radio::make('price_preset_id')
                                ->label('Prix prédéfini')
                                ->options($presets->mapWithKeys(fn (PricePreset $p): array => [
                                    $p->id => $p->label . ' ' . $p->formatted_amount . ($p->description ? ' · ' . $p->description : ''),
                                ])->all())
                                ->required(fn ($get): bool => $get('price_mode') === 'preset')
                                ->visible(fn ($get): bool => $get('price_mode') === 'preset')
                                ->reactive()
                                ->afterStateUpdated(function ($state, $set) use ($presets): void {
                                    $preset = $presets->firstWhere('id', $state);
                                    if ($preset) {
                                        $set('amount_eur', number_format($preset->amount_cents / 100, 2, '.', ''));
                                    }
                                }),

                            TextInput::make('amount_eur')
                                ->label('Montant')
                                ->numeric()
                                ->required()
                                ->prefix('€')
                                ->minValue(1)
                                ->step('0.01')
                                ->helperText('Ex: 29,90')
                                ->visible(fn ($get): bool => $get('price_mode') === 'custom')
                                ->afterStateHydrated(function ($state, $set) use ($presets): void {
                                    if ($state !== null) {
                                        return;
                                    }

                                    $firstPreset = $presets->first();

                                    if ($firstPreset) {
                                        $set('price_preset_id', $firstPreset->id);
                                        $set('amount_eur', number_format($firstPreset->amount_cents / 100, 2, '.', ''));
                                    }
                                }),

                            Select::make('expiry_days')
                                ->label("Durée de validité")
                                ->options([
                                    3 => '3 jours',
                                    7 => '7 jours',
                                    14 => '14 jours',
                                ])
                                ->default(7)
                                ->required(),

                            Textarea::make('note_to_author')
                                ->label('Message au rédacteur (optionnel)')
                                ->rows(4)
                                ->maxLength(2000)
                                ->helperText('Visible dans l\'email envoyé au rédacteur.'),
                        ];
                    })
                    ->action(function (Submission $record, array $data): void {
                        $amountCents = (int) round(((float) ($data['amount_eur'] ?? 0)) * 100);

                        // Si preset, on récupère le montant depuis le preset
                        if (($data['price_mode'] ?? 'preset') === 'preset' && isset($data['price_preset_id'])) {
                            $preset = PricePreset::find($data['price_preset_id']);
                            $amountCents = $preset?->amount_cents ?? $amountCents;
                        }

                        $quote = app(PublicationQuoteService::class)->propose(
                            submission:    $record,
                            moderator:     auth()->user(),
                            amountCents:   $amountCents,
                            pricePresetId: $data['price_preset_id'] ?? null,
                            noteToAuthor:  $data['note_to_author'] ?? null,
                            expiryDays:    (int) ($data['expiry_days'] ?? 7),
                        );

                        Mail::to($record->user->email)
                            ->queue(new \App\Mail\QuoteSentMail($record->fresh(['user']), $quote));
                    })
                    ->successNotification(fn (Submission $r) => Notification::make()->success()->title('Proposition envoyée')->body('Le rédacteur de "' . $r->title . '" a reçu la proposition de paiement.')),

                // ── Rejeter ──────────────────────────────────────────
                TableAction::make('reject')
                    ->label('Rejeter')
                    ->icon(Heroicon::OutlinedXCircle)
                    ->color('danger')
                    ->visible(fn (Submission $record): bool => in_array($record->status, ['submitted', 'pending', 'under_review', 'price_proposed', 'changes_requested'], true))
                    ->form([
                        Textarea::make('notes')
                            ->label('Motif du rejet')
                            ->rows(4)
                            ->required()
                            ->maxLength(2000)
                            ->helperText('Ce message sera envoyé au rédacteur.'),
                    ])
                    ->requiresConfirmation()
                    ->modalHeading('Rejeter cette soumission ?')
                    ->modalDescription('Le rédacteur recevra un email avec le motif du refus.')
                    ->action(function (Submission $record, array $data): void {
                        if (in_array($record->status, ['submitted', 'pending'], true)) {
                            app(SubmissionWorkflowService::class)->startReview($record, auth()->user());
                            $record->refresh();
                        }
                        app(SubmissionWorkflowService::class)->reject($record, auth()->user(), $data['notes']);
                    })
                    ->successNotification(fn (Submission $r) => Notification::make()->success()->title('Soumission rejetée')->body('"' . $r->title . '" a été rejetée et le rédacteur notifié.')),
            ])
            ->defaultSort('created_at', 'desc');
    }

    /* ------------------------------------------------------------------ */
    /*  Pages                                                             */
    /* ------------------------------------------------------------------ */

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSubmissions::route('/'),
            'view'  => ViewSubmission::route('/{record}'),
        ];
    }
}
