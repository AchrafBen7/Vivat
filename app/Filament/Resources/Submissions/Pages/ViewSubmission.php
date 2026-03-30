<?php

namespace App\Filament\Resources\Submissions\Pages;

use App\Filament\Resources\Submissions\SubmissionResource;
use App\Models\Category;
use App\Models\User;
use App\Services\PaymentRefundService;
use App\Services\SubmissionPublishingService;
use Filament\Actions\Action;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Filament\Resources\Pages\ViewRecord;

class ViewSubmission extends ViewRecord
{
    protected static string $resource = SubmissionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('updateReview')
                ->label('Mettre a jour la revue')
                ->icon(Heroicon::OutlinedClipboardDocumentCheck)
                ->color('gray')
                ->form([
                    Select::make('reviewed_by')
                        ->label('Relue par')
                        ->options(fn (): array => User::query()->role('admin')->orderBy('name')->pluck('name', 'id')->all())
                        ->default(fn (): ?string => $this->record->reviewed_by ?: auth()->id())
                        ->searchable()
                        ->helperText('Sélectionnez la personne qui a relu ou validé cette soumission.')
                        ->required(),
                    DateTimePicker::make('reviewed_at')
                        ->label('Relue le')
                        ->seconds(false)
                        ->default(fn () => $this->record->reviewed_at ?: now())
                        ->helperText('Indiquez la date réelle de relecture si nécessaire.')
                        ->required(),
                    Textarea::make('reviewer_notes')
                        ->label('Note admin')
                        ->rows(5)
                        ->default(fn (): ?string => $this->record->reviewer_notes)
                        ->helperText('Ajoutez un retour utile pour le suivi éditorial.')
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
                        ->body('Les informations de relecture ont bien été enregistrées.')
                ),
            Action::make('approve')
                ->label('Approuver et publier')
                ->icon(Heroicon::OutlinedCheckCircle)
                ->color('success')
                ->visible(fn (): bool => $this->record->status === 'pending')
                ->form([
                    Select::make('category_id')
                        ->label('Catégorie de publication')
                        ->options(fn (): array => Category::query()->orderBy('name')->pluck('name', 'id')->all())
                        ->default(fn (): ?string => $this->record->category_id)
                        ->searchable()
                        ->helperText('Choisissez la rubrique dans laquelle l’article apparaîtra.')
                        ->required(),
                    Select::make('article_type')
                        ->label("Type d'article")
                        ->options([
                            'hot_news' => 'Hot news',
                            'standard' => 'Standard',
                            'long_form' => 'Long form',
                        ])
                        ->default('standard')
                        ->helperText('Ce choix influence le style et la mise en avant sur la home.')
                        ->required(),
                    Select::make('reviewed_by')
                        ->label('Relue par')
                        ->options(fn (): array => User::query()->role('admin')->orderBy('name')->pluck('name', 'id')->all())
                        ->default(fn (): ?string => $this->record->reviewed_by ?: auth()->id())
                        ->searchable()
                        ->helperText('Sélectionnez l’admin responsable de la décision.')
                        ->required(),
                    DateTimePicker::make('reviewed_at')
                        ->label('Relue le')
                        ->seconds(false)
                        ->default(fn () => $this->record->reviewed_at ?: now())
                        ->helperText('Vous pouvez ajuster la date si la validation a eu lieu plus tôt.')
                        ->required(),
                    Textarea::make('reviewer_notes')
                        ->label('Note admin')
                        ->rows(4)
                        ->default(fn (): ?string => $this->record->reviewer_notes)
                        ->helperText('Optionnel. Ajoutez un contexte éditorial ou un retour à garder dans l’historique.')
                        ->maxLength(2000),
                ])
                ->action(function (array $data): void {
                    app(SubmissionPublishingService::class)->approveAndPublish(
                        submission: $this->record,
                        data: array_merge($data, ['notes' => $data['reviewer_notes'] ?? null]),
                        reviewer: auth()->user(),
                    );

                    $this->record->refresh();
                })
                ->successNotification(
                    fn () => Notification::make()
                        ->success()
                        ->title('Article publié')
                        ->body('La soumission a été approuvée et l’article est maintenant visible sur le site.')
                ),
            Action::make('reject')
                ->label('Rejeter')
                ->icon(Heroicon::OutlinedXCircle)
                ->color('danger')
                ->visible(fn (): bool => $this->record->status === 'pending')
                ->form([
                    Select::make('reviewed_by')
                        ->label('Relue par')
                        ->options(fn (): array => User::query()->role('admin')->orderBy('name')->pluck('name', 'id')->all())
                        ->default(fn (): ?string => $this->record->reviewed_by ?: auth()->id())
                        ->searchable()
                        ->helperText('Sélectionnez l’admin qui envoie ce retour.')
                        ->required(),
                    DateTimePicker::make('reviewed_at')
                        ->label('Relue le')
                        ->seconds(false)
                        ->default(fn () => $this->record->reviewed_at ?: now())
                        ->helperText('Ajustez la date si besoin.')
                        ->required(),
                    Textarea::make('reviewer_notes')
                        ->label('Note admin / motif du rejet')
                        ->rows(4)
                        ->required()
                        ->default(fn (): ?string => $this->record->reviewer_notes)
                        ->helperText('Expliquez clairement au rédacteur ce qui doit être corrigé avant un nouvel envoi.')
                        ->maxLength(2000),
                ])
                ->action(function (array $data): void {
                    app(SubmissionPublishingService::class)->reject(
                        submission: $this->record,
                        data: array_merge($data, ['notes' => $data['reviewer_notes'] ?? null]),
                        reviewer: auth()->user(),
                    );

                    $this->record->refresh();
                })
                ->successNotification(
                    fn () => Notification::make()
                        ->success()
                        ->title('Soumission rejetée')
                        ->body('Le retour éditorial a bien été enregistré. Le rédacteur peut corriger puis renvoyer sa soumission.')
                ),
            Action::make('refund')
                ->label('Rembourser')
                ->icon(Heroicon::OutlinedArrowUturnLeft)
                ->color('danger')
                ->visible(fn (): bool => (bool) $this->record->payment?->isRefundable())
                ->form([
                    Textarea::make('reason')
                        ->label('Motif du remboursement')
                        ->rows(4)
                        ->default('Article refusé')
                        ->helperText('Ce message sera inclus dans l’email de remboursement envoyé au rédacteur.')
                        ->maxLength(255),
                ])
                ->requiresConfirmation()
                ->modalHeading('Rembourser ce paiement ?')
                ->modalDescription('Le paiement Stripe sera remboursé immédiatement si la transaction est éligible.')
                ->action(function (array $data): void {
                    app(PaymentRefundService::class)->refund(
                        $this->record->payment,
                        $data['reason'] ?? 'Article refusé',
                    );

                    $this->record->refresh();
                })
                ->successNotification(
                    fn () => Notification::make()
                        ->success()
                        ->title('Paiement remboursé')
                        ->body('Le remboursement a été traité et un email a été envoyé au rédacteur.')
                ),
        ];
    }
}
