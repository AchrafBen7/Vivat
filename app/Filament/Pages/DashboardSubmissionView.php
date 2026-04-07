<?php

namespace App\Filament\Pages;

use App\Models\Category;
use App\Models\Submission;
use App\Models\User;
use App\Services\PaymentRefundService;
use App\Services\SubmissionPublishingService;
use Filament\Actions\Action;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;

class DashboardSubmissionView extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;

    protected static string|\UnitEnum|null $navigationGroup = 'Editorial';

    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $slug = 'editorial/soumissions/{record}';

    protected static ?string $title = 'Afficher soumission';

    protected string $view = 'filament.pages.dashboard-submission-view';

    public Submission $record;

    public function mount(string $record): void
    {
        $this->record = Submission::query()
            ->with(['user', 'category', 'reviewer', 'payment', 'publishedArticle'])
            ->findOrFail($record);
    }

    public function getSubmissionData(): array
    {
        return [
            'id' => $this->record->id,
            'title' => $this->record->title,
            'status' => $this->record->status,
            'cover' => $this->record->cover_image_url,
            'author' => $this->record->user?->name ?? 'Auteur inconnu',
            'author_email' => $this->record->user?->email ?? '',
            'category' => $this->record->category?->name ?? 'Sans catégorie',
            'reading_time' => (int) ($this->record->reading_time ?: 5),
            'created_at' => $this->record->created_at?->format('d/m/Y à H:i') ?? 'Date inconnue',
            'excerpt' => (string) ($this->record->excerpt ?: ''),
            'content' => (string) ($this->record->content ?: ''),
            'reviewer' => $this->record->reviewer?->name,
            'reviewed_at' => $this->record->reviewed_at?->format('d/m/Y à H:i'),
            'reviewer_notes' => $this->record->reviewer_notes,
            'payment_status' => $this->record->payment?->status,
            'payment_amount' => $this->record->payment
                ? number_format($this->record->payment->amount / 100, 2, ',', ' ') . ' ' . strtoupper($this->record->payment->currency)
                : null,
            'refund_reason' => $this->record->payment?->refund_reason,
            'preview_url' => route('contributor.articles.show', ['submission' => $this->record->slug]),
            'is_revision' => filled($this->record->published_article_id),
            'depublication_requested' => $this->record->depublication_requested_at !== null,
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('back')
                ->label('Retour aux soumissions')
                ->icon(Heroicon::OutlinedArrowLeft)
                ->color('gray')
                ->url(DashboardSubmissions::getUrl()),
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
                        ->required(),
                    DateTimePicker::make('reviewed_at')
                        ->label('Relue le')
                        ->seconds(false)
                        ->default(fn () => $this->record->reviewed_at ?: now())
                        ->required(),
                    Textarea::make('reviewer_notes')
                        ->label('Note admin')
                        ->rows(5)
                        ->default(fn (): ?string => $this->record->reviewer_notes)
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
                    Select::make('reviewed_by')
                        ->label('Relue par')
                        ->options(fn (): array => User::query()->role('admin')->orderBy('name')->pluck('name', 'id')->all())
                        ->default(fn (): ?string => $this->record->reviewed_by ?: auth()->id())
                        ->searchable()
                        ->required(),
                    DateTimePicker::make('reviewed_at')
                        ->label('Relue le')
                        ->seconds(false)
                        ->default(fn () => $this->record->reviewed_at ?: now())
                        ->required(),
                    Textarea::make('reviewer_notes')
                        ->label('Note admin')
                        ->rows(4)
                        ->default(fn (): ?string => $this->record->reviewer_notes)
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
                        ->body('La soumission a été approuvée et publiée.')
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
                        ->required(),
                    DateTimePicker::make('reviewed_at')
                        ->label('Relue le')
                        ->seconds(false)
                        ->default(fn () => $this->record->reviewed_at ?: now())
                        ->required(),
                    Textarea::make('reviewer_notes')
                        ->label('Note admin / motif du rejet')
                        ->rows(4)
                        ->required()
                        ->default(fn (): ?string => $this->record->reviewer_notes)
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
                        ->body('Le retour éditorial a bien été enregistré.')
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
                        ->body('Le remboursement a été traité.')
                ),
        ];
    }
}
