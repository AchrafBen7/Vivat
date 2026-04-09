<?php

namespace App\Filament\Resources\Payments;

use App\Filament\Resources\Payments\Pages\ListPayments;
use App\Models\Payment;
use App\Services\PaymentRefundService;
use BackedEnum;
use Filament\Actions\Action as TableAction;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\HtmlString;

class PaymentResource extends Resource
{
    protected static ?string $model = Payment::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCreditCard;

    protected static ?string $navigationLabel = 'Paiements';

    protected static ?string $modelLabel = 'paiement';

    protected static ?string $pluralModelLabel = 'paiements';

    protected static string|\UnitEnum|null $navigationGroup = 'Editorial';

    protected static bool $shouldRegisterNavigation = false;

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query
                ->with(['user', 'submission'])
                ->where(function (Builder $innerQuery): Builder {
                    return $innerQuery
                        ->where('status', '!=', 'pending')
                        ->orWhereHas('submission', fn (Builder $submissionQuery) => $submissionQuery->where('status', '!=', 'draft'));
                })
                ->orderByRaw('CASE WHEN submission_id IS NULL THEN 1 ELSE 0 END')
                ->orderByRaw("FIELD(status, 'pending', 'paid', 'refunded', 'failed', 'abandoned')")
                ->orderByDesc('created_at'))
            ->columns([
                TextColumn::make('submission.title')
                    ->label('Paiement')
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query->whereHas('submission', fn (Builder $submissionQuery) => $submissionQuery->where('title', 'like', '%' . $search . '%'))
                            ->orWhereHas('user', fn (Builder $userQuery) => $userQuery->where('email', 'like', '%' . $search . '%')->orWhere('name', 'like', '%' . $search . '%'));
                    })
                    ->wrap()
                    ->weight(FontWeight::SemiBold)
                    ->lineClamp(2)
                    ->formatStateUsing(fn (?string $state, Payment $record): string => $state ?: 'Paiement abandonné ou non relié')
                    ->description(function (Payment $record): HtmlString {
                        $author = e($record->user?->name ?? 'Utilisateur inconnu');
                        $email = e($record->user?->email ?? 'Email indisponible');
                        $submissionStatus = e(match ($record->submission?->status) {
                            'draft' => 'Brouillon',
                            'pending' => 'En attente',
                            'approved' => 'Approuvée',
                            'rejected' => 'Rejetée',
                            default => $record->submission?->status ?? 'N/A',
                        });
                        $date = e($record->created_at?->format('d/m/Y à H:i') ?? 'Date inconnue');

                        if (! $record->submission) {
                            return new HtmlString(
                                '<div class="mt-1 space-y-1 text-xs text-gray-500">'
                                . '<div><span class="font-medium text-gray-700">' . $author . '</span> · ' . $email . '</div>'
                                . '<div>Paiement créé le ' . $date . '</div>'
                                . "<div class=\"text-gray-600\">Aucune soumission n'est liée à cette transaction. Il s'agit généralement d'une tentative abandonnée ou d'un cas technique.</div>"
                                . '</div>'
                            );
                        }

                        if ($record->status === 'pending' && $record->submission->status === 'draft') {
                            return new HtmlString(
                                '<div class="mt-1 space-y-1 text-xs text-gray-500">'
                                . '<div><span class="font-medium text-gray-700">' . $author . '</span> · ' . $email . '</div>'
                                . '<div>Brouillon non soumis · ' . $date . '</div>'
                                . '<div class="text-gray-600">Le rédacteur a initié le paiement sans le finaliser. Ce cas est masqué par défaut dans la liste.</div>'
                                . '</div>'
                            );
                        }

                        return new HtmlString(
                            '<div class="mt-1 space-y-1 text-xs text-gray-500">'
                            . '<div><span class="font-medium text-gray-700">' . $author . '</span> · ' . $email . '</div>'
                            . '<div>Soumission : ' . $submissionStatus . ' · ' . $date . '</div>'
                            . '</div>'
                        );
                    })
                    ->html(),
                TextColumn::make('amount')
                    ->label('Montant')
                    ->weight(FontWeight::SemiBold)
                    ->formatStateUsing(fn (int $state, Payment $record): string => number_format($state / 100, 2, ',', ' ') . ' ' . strtoupper($record->currency)),
                TextColumn::make('status')
                    ->label('Statut')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pending' => 'En attente',
                        'paid' => 'Payé',
                        'refunded' => 'Remboursé',
                        'failed' => 'Échoué',
                        'abandoned' => 'Abandonné',
                        default => ucfirst($state),
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'paid' => 'success',
                        'refunded' => 'gray',
                        'failed' => 'danger',
                        'abandoned' => 'gray',
                        default => 'gray',
                    })
                    ->description(fn (Payment $record): ?string => ! $record->submission && $record->status === 'pending'
                        ? 'À vérifier'
                        : null),
                TextColumn::make('refund_reason')
                    ->label('Remboursement')
                    ->wrap()
                    ->placeholder('Aucun'),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'pending' => 'En attente',
                        'paid' => 'Payé',
                        'refunded' => 'Remboursé',
                        'failed' => 'Échoué',
                        'abandoned' => 'Abandonné',
                    ]),
                SelectFilter::make('submission_status')
                    ->label('Statut de la soumission')
                    ->options([
                        'draft' => 'Brouillon',
                        'pending' => 'En attente',
                        'approved' => 'Approuvée',
                        'rejected' => 'Rejetée',
                    ])
                    ->query(fn (Builder $query, array $data): Builder => $query->when(
                        $data['value'] ?? null,
                        fn (Builder $innerQuery, string $status): Builder => $innerQuery->whereHas('submission', fn (Builder $submissionQuery) => $submissionQuery->where('status', $status))
                    )),
                SelectFilter::make('link_state')
                    ->label('Liaison article')
                    ->options([
                        'linked' => 'Avec soumission',
                        'orphan' => 'Sans soumission liée',
                    ])
                    ->query(fn (Builder $query, array $data): Builder => match ($data['value'] ?? null) {
                        'linked' => $query->whereNotNull('submission_id'),
                        'orphan' => $query->whereNull('submission_id'),
                        default => $query,
                    }),
            ])
            ->paginated([10, 25, 50])
            ->defaultPaginationPageOption(10)
            ->recordActions([
                TableAction::make('open_submission')
                    ->label('Voir la soumission')
                    ->icon(Heroicon::OutlinedDocumentText)
                    ->color('gray')
                    ->visible(fn (Payment $record): bool => (bool) $record->submission)
                    ->url(fn (Payment $record): ?string => $record->submission
                        ? \App\Filament\Resources\Submissions\SubmissionResource::getUrl('view', ['record' => $record->submission])
                        : null),
                TableAction::make('refund')
                    ->label('Rembourser')
                    ->icon(Heroicon::OutlinedArrowUturnLeft)
                    ->color('danger')
                    ->visible(fn (Payment $record): bool => $record->isRefundable())
                    ->form([
                        \Filament\Forms\Components\Textarea::make('reason')
                            ->label('Motif du remboursement')
                            ->rows(4)
                            ->default('Article refusé')
                            ->helperText('Ce message sera inclus dans le mail envoyé au rédacteur.')
                            ->maxLength(255),
                    ])
                    ->requiresConfirmation()
                    ->modalHeading('Rembourser ce paiement ?')
                    ->modalDescription('Stripe traitera le remboursement immédiatement si la transaction est éligible.')
                    ->action(function (Payment $record, array $data): void {
                        app(PaymentRefundService::class)->refund(
                            $record->loadMissing('submission.user'),
                            $data['reason'] ?? 'Article refusé',
                        );
                    })
                    ->successNotification(
                        fn (Payment $record) => Notification::make()
                            ->success()
                            ->title('Paiement remboursé')
                            ->body('Le remboursement lié à "' . ($record->submission?->title ?? 'cette soumission') . '" a été traité.')
                    ),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPayments::route('/'),
        ];
    }
}
