<?php

namespace App\Filament\Resources\Submissions\Pages;

use App\Filament\Resources\Submissions\SubmissionResource;
use App\Models\Category;
use App\Models\User;
use App\Services\SubmissionPublishingService;
use Filament\Actions\Action;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
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
                ->successNotificationTitle('Informations de revue mises a jour.'),
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
                ->successNotificationTitle('Soumission approuvée et article publié.'),
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
                ->successNotificationTitle('Soumission rejetée.'),
        ];
    }
}
