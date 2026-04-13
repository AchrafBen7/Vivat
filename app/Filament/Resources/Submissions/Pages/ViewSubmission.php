<?php

namespace App\Filament\Resources\Submissions\Pages;

use App\Filament\Resources\Submissions\SubmissionResource;
use App\Models\PricePreset;
use App\Models\User;
use App\Services\PublicationQuoteService;
use App\Services\SubmissionWorkflowService;
use Filament\Actions\Action;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Filament\Resources\Pages\ViewRecord;

class ViewSubmission extends ViewRecord
{
    protected static string $resource = SubmissionResource::class;

    protected string $view = 'filament.resources.submissions.pages.view-submission';

    public function getSubmissionData(): array
    {
        $record = $this->record->loadMissing([
            'user',
            'category',
            'reviewer',
            'quote.preset',
            'quote.proposedBy',
            'latestSubmissionPayment',
            'statusLogs.triggeredBy',
            'publishedArticle',
        ]);

        return [
            'id' => $record->id,
            'title' => $record->title,
            'status' => $record->status,
            'cover' => $record->cover_image_url,
            'author' => $record->user?->name ?? 'Auteur inconnu',
            'author_email' => $record->user?->email ?? '',
            'category' => $record->category?->name ?? 'Sans catégorie',
            'reading_time' => (int) ($record->reading_time ?: 5),
            'created_at' => $record->submitted_at?->format('d/m/Y à H:i') ?? $record->created_at?->format('d/m/Y à H:i') ?? 'Date inconnue',
            'excerpt' => (string) ($record->excerpt ?: ''),
            'content' => (string) ($record->content ?: ''),
            'reviewer' => $record->reviewer?->name,
            'reviewed_at' => $record->reviewed_at?->format('d/m/Y à H:i'),
            'reviewer_notes' => $record->reviewer_notes,
            'payment_status' => $record->latestSubmissionPayment?->status,
            'payment_amount' => $record->latestSubmissionPayment?->formatted_amount,
            'payment_failure' => $record->latestSubmissionPayment?->failure_message,
            'refund_reason' => $record->latestSubmissionPayment?->refund_reason,
            'quote_amount' => $record->quote?->formatted_amount,
            'quote_status' => $record->quote?->status,
            'quote_type' => $record->quote?->article_type,
            'quote_preset' => $record->quote?->preset?->label,
            'quote_expires_at' => $record->quote?->expires_at?->format('d/m/Y à H:i'),
            'quote_note' => $record->quote?->note_to_author,
            'quote_moderator' => $record->quote?->proposedBy?->name,
            'preview_url' => route('contributor.articles.show', ['submission' => $record->slug]),
            'published_url' => $record->publishedArticle?->slug ? url('/articles/' . $record->publishedArticle->slug) : null,
            'is_revision' => filled($record->published_article_id),
            'depublication_requested' => $record->depublication_requested_at !== null,
            'status_logs' => $record->statusLogs
                ->sortByDesc('created_at')
                ->take(10)
                ->map(fn ($log): array => [
                    'from' => $log->from_status,
                    'to' => $log->to_status,
                    'reason' => $log->reason,
                    'by' => $log->triggeredBy?->name ?? match ($log->trigger_source) {
                        'system' => 'Système',
                        'stripe_webhook' => 'Stripe',
                        'author' => 'Rédacteur',
                        default => 'Admin',
                    },
                    'at' => $log->created_at?->format('d/m/Y à H:i') ?? '',
                ])
                ->values()
                ->all(),
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('preview')
                ->label('Aperçu')
                ->icon(Heroicon::OutlinedEye)
                ->color('gray')
                ->url(fn (): string => route('contributor.articles.show', ['submission' => $this->record->slug]))
                ->openUrlInNewTab(),
            Action::make('updateReview')
                ->label('Mettre à jour la revue')
                ->icon(Heroicon::OutlinedClipboardDocumentCheck)
                ->color('gray')
                ->form([
                    Select::make('reviewed_by')
                        ->label('Relue par')
                        ->options(fn (): array => User::query()->role('admin')->orderBy('name')->pluck('name', 'id')->all())
                        ->default(fn (): ?string => $this->record->reviewed_by ?: auth()->id())
                        ->searchable()
                        ->helperText('Sélectionnez la personne qui suit cette soumission.')
                        ->required(),
                    DateTimePicker::make('reviewed_at')
                        ->label('Relue le')
                        ->seconds(false)
                        ->default(fn () => $this->record->reviewed_at ?: now())
                        ->helperText('Ajustez la date réelle de review si nécessaire.')
                        ->required(),
                    Textarea::make('reviewer_notes')
                        ->label('Notes admin')
                        ->rows(5)
                        ->default(fn (): ?string => $this->record->reviewer_notes)
                        ->helperText("Notes internes ou retour éditorial pour l'historique.")
                        ->maxLength(2000),
                ])
                ->action(function (array $data): void {
                    $this->record->update([
                        'reviewed_by' => $data['reviewed_by'],
                        'reviewed_at' => $data['reviewed_at'],
                        'reviewer_notes' => $data['reviewer_notes'] ?? null,
                    ]);

                    $this->record->refresh();
                })
                ->successNotification(
                    fn () => Notification::make()
                        ->success()
                        ->title('Revue mise à jour')
                        ->body('Les informations de modération ont bien été enregistrées.')
                ),
            Action::make('startReview')
                ->label('Démarrer la revue')
                ->icon(Heroicon::OutlinedEye)
                ->color('info')
                ->visible(fn (): bool => in_array($this->record->status, ['submitted', 'pending'], true))
                ->action(function (): void {
                    app(SubmissionWorkflowService::class)->startReview($this->record, auth()->user());
                    $this->record->refresh();
                })
                ->successNotification(
                    fn () => Notification::make()
                        ->info()
                        ->title('Revue démarrée')
                        ->body('La soumission est maintenant en cours de revue.')
                ),
            Action::make('requestChanges')
                ->label('Demander des corrections')
                ->icon(Heroicon::OutlinedPencil)
                ->color('warning')
                ->visible(fn (): bool => $this->record->status === 'under_review')
                ->form([
                    Textarea::make('note')
                        ->label('Message au rédacteur')
                        ->rows(5)
                        ->required()
                        ->maxLength(3000)
                        ->helperText('Expliquez précisément les points à corriger avant un nouvel envoi.'),
                ])
                ->action(function (array $data): void {
                    app(SubmissionWorkflowService::class)->requestChanges(
                        $this->record,
                        auth()->user(),
                        $data['note'],
                    );

                    $this->record->refresh();
                })
                ->successNotification(
                    fn () => Notification::make()
                        ->warning()
                        ->title('Corrections demandées')
                        ->body('Le rédacteur a été notifié et peut renvoyer sa soumission.')
                ),
            Action::make('proposePrice')
                ->label('Proposer un prix')
                ->icon(Heroicon::OutlinedCurrencyEuro)
                ->color('success')
                ->visible(fn (): bool => $this->record->status === 'under_review')
                ->form(function (): array {
                    $presets = PricePreset::active()->get();
                    $defaultPreset = $presets->first();

                    return [
                        Select::make('article_type')
                            ->label("Type d'article")
                            ->options([
                                'standard' => 'Standard',
                                'hot_news' => 'Hot news',
                                'long_form' => 'Long format',
                            ])
                            ->default('standard')
                            ->required()
                            ->helperText("Le format qui sera utilisé au moment de la publication après paiement."),
                        Radio::make('price_mode')
                            ->label('Type de tarif')
                            ->options([
                                'preset' => 'Tarif prédéfini',
                                'custom' => 'Montant libre',
                            ])
                            ->default($defaultPreset ? 'preset' : 'custom')
                            ->reactive(),
                        Radio::make('price_preset_id')
                            ->label('Tarifs fixes')
                            ->options($presets->mapWithKeys(fn (PricePreset $preset): array => [
                                $preset->id => $preset->label . ' ' . $preset->formatted_amount . ($preset->description ? ' · ' . $preset->description : ''),
                            ])->all())
                            ->default($defaultPreset?->id)
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
                            ->prefix('€')
                            ->numeric()
                            ->required()
                            ->minValue(1)
                            ->step('0.01')
                            ->default($defaultPreset ? number_format($defaultPreset->amount_cents / 100, 2, '.', '') : null)
                            ->helperText('Le montant payé par le rédacteur avant publication.')
                            ->visible(fn ($get): bool => $get('price_mode') === 'custom')
                            ->afterStateHydrated(function ($state, $set) use ($defaultPreset): void {
                                if ($state !== null || ! $defaultPreset) {
                                    return;
                                }

                                $set('amount_eur', number_format($defaultPreset->amount_cents / 100, 2, '.', ''));
                            }),
                        Select::make('expiry_days')
                            ->label('Validité de la demande')
                            ->options([
                                3 => '3 jours',
                                7 => '7 jours',
                                14 => '14 jours',
                            ])
                            ->default(7)
                            ->required(),
                        Textarea::make('note_to_author')
                            ->label('Message au rédacteur')
                            ->rows(4)
                            ->maxLength(2000)
                            ->helperText("Ce message sera inclus dans l'email de demande de paiement."),
                    ];
                })
                ->action(function (array $data): void {
                    $amountCents = (int) round(((float) ($data['amount_eur'] ?? 0)) * 100);

                    if (($data['price_mode'] ?? 'preset') === 'preset' && isset($data['price_preset_id'])) {
                        $preset = PricePreset::find($data['price_preset_id']);
                        $amountCents = $preset?->amount_cents ?? $amountCents;
                    }

                    app(PublicationQuoteService::class)->propose(
                        submission: $this->record,
                        moderator: auth()->user(),
                        amountCents: $amountCents,
                        articleType: $data['article_type'] ?? 'standard',
                        pricePresetId: $data['price_preset_id'] ?? null,
                        noteToAuthor: $data['note_to_author'] ?? null,
                        expiryDays: (int) ($data['expiry_days'] ?? 7),
                    );

                    $this->record->refresh();
                })
                ->successNotification(
                    fn () => Notification::make()
                        ->success()
                        ->title('Prix proposé')
                        ->body('La demande de paiement a bien été envoyée au rédacteur.')
                ),
            Action::make('reject')
                ->label('Rejeter')
                ->icon(Heroicon::OutlinedXCircle)
                ->color('danger')
                ->visible(fn (): bool => in_array($this->record->status, ['submitted', 'pending', 'under_review', 'price_proposed', 'changes_requested'], true))
                ->form([
                    Textarea::make('reason')
                        ->label('Motif du rejet')
                        ->rows(4)
                        ->required()
                        ->maxLength(2000)
                        ->helperText('Ce message sera envoyé au rédacteur.'),
                ])
                ->requiresConfirmation()
                ->modalHeading('Rejeter cette soumission ?')
                ->modalDescription('Le rédacteur recevra un email avec le motif du refus.')
                ->action(function (array $data): void {
                    if (in_array($this->record->status, ['submitted', 'pending'], true)) {
                        app(SubmissionWorkflowService::class)->startReview($this->record, auth()->user());
                        $this->record->refresh();
                    }

                    app(SubmissionWorkflowService::class)->reject(
                        $this->record,
                        auth()->user(),
                        $data['reason'],
                    );

                    $this->record->refresh();
                })
                ->successNotification(
                    fn () => Notification::make()
                        ->success()
                        ->title('Soumission rejetée')
                        ->body('Le refus a été enregistré et le rédacteur a été notifié.')
                ),
        ];
    }
}
