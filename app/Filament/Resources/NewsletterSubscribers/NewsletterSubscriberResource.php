<?php

namespace App\Filament\Resources\NewsletterSubscribers;

use App\Filament\Resources\NewsletterSubscribers\Pages\ListNewsletterSubscribers;
use App\Models\NewsletterSubscriber;
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

class NewsletterSubscriberResource extends Resource
{
    protected static ?string $model = NewsletterSubscriber::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedEnvelope;

    protected static ?string $navigationLabel = 'Newsletter';

    protected static ?string $modelLabel = 'abonne';

    protected static ?string $pluralModelLabel = 'abonnes newsletter';

    protected static string|\UnitEnum|null $navigationGroup = 'Editorial';

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(
                fn (Builder $query) => $query
                    ->orderByRaw('CASE WHEN unsubscribed_at IS NULL THEN 0 ELSE 1 END')
                    ->orderByDesc('confirmed')
                    ->orderByDesc('created_at')
            )
            ->columns([
                TextColumn::make('email')
                    ->label('Abonne')
                    ->searchable()
                    ->wrap()
                    ->weight(FontWeight::SemiBold)
                    ->description(function (NewsletterSubscriber $record): HtmlString {
                        $name = trim((string) ($record->name ?? ''));
                        $joinedAt = $record->created_at?->format('d/m/Y');
                        $interests = collect($record->interests ?? [])
                            ->filter()
                            ->map(fn (string $interest): string => str($interest)->replace('-', ' ')->title())
                            ->take(3)
                            ->implode(', ');

                        $details = [];

                        if ($name !== '') {
                            $details[] = '<span class="font-medium text-gray-700">' . e($name) . '</span>';
                        }

                        if ($joinedAt) {
                            $details[] = 'Inscrit le ' . e($joinedAt);
                        }

                        if ($interests !== '') {
                            $details[] = 'Centres d’interet: ' . e($interests);
                        }

                        return new HtmlString(
                            '<div class="mt-1 space-y-1 text-xs text-gray-500">'
                            . implode('<br>', $details)
                            . '</div>'
                        );
                    })
                    ->html(),
                TextColumn::make('confirmed')
                    ->label('Statut')
                    ->badge()
                    ->alignCenter()
                    ->formatStateUsing(function (bool $state, NewsletterSubscriber $record): string {
                        if ($record->unsubscribed_at) {
                            return 'Desinscrit';
                        }

                        return $state ? 'Confirme' : 'En attente';
                    })
                    ->color(function (bool $state, NewsletterSubscriber $record): string {
                        if ($record->unsubscribed_at) {
                            return 'danger';
                        }

                        return $state ? 'success' : 'warning';
                    })
                    ->icon(function (bool $state, NewsletterSubscriber $record): Heroicon {
                        if ($record->unsubscribed_at) {
                            return Heroicon::OutlinedNoSymbol;
                        }

                        return $state ? Heroicon::OutlinedCheckCircle : Heroicon::OutlinedClock;
                    })
                    ->description(function (NewsletterSubscriber $record): ?string {
                        if ($record->unsubscribed_at) {
                            return 'Le ' . $record->unsubscribed_at->format('d/m/Y H:i');
                        }

                        if ($record->confirmed_at) {
                            return 'Le ' . $record->confirmed_at->format('d/m/Y H:i');
                        }

                        return null;
                    }),
            ])
            ->filters([
                SelectFilter::make('state')
                    ->label('Etat')
                    ->options([
                        'active' => 'Confirmes',
                        'pending' => 'En attente',
                        'unsubscribed' => 'Desinscrits',
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return match ($data['value'] ?? null) {
                            'active' => $query->where('confirmed', true)->whereNull('unsubscribed_at'),
                            'pending' => $query->where('confirmed', false)->whereNull('unsubscribed_at'),
                            'unsubscribed' => $query->whereNotNull('unsubscribed_at'),
                            default => $query,
                        };
                    }),
            ])
            ->paginated([10, 25, 50])
            ->defaultPaginationPageOption(10)
            ->recordActions([
                TableAction::make('copyEmail')
                    ->label('Copier email')
                    ->icon(Heroicon::OutlinedClipboardDocument)
                    ->color('gray')
                    ->action(function (NewsletterSubscriber $record): void {
                        request()->session()->flash('newsletter_copied_email', $record->email);

                        Notification::make()
                            ->title('Email pret a copier')
                            ->body($record->email)
                            ->success()
                            ->send();
                    }),
                TableAction::make('unsubscribe')
                    ->label('Desinscrire')
                    ->icon(Heroicon::OutlinedNoSymbol)
                    ->color('danger')
                    ->visible(fn (NewsletterSubscriber $record): bool => $record->unsubscribed_at === null)
                    ->requiresConfirmation()
                    ->modalHeading('Desinscrire cet abonne ?')
                    ->modalDescription('Le lien newsletter ne sera plus actif pour cette adresse.')
                    ->action(function (NewsletterSubscriber $record): void {
                        $record->unsubscribe();

                        Notification::make()
                            ->title('Abonne desinscrit')
                            ->body('La desinscription a bien ete prise en compte.')
                            ->success()
                            ->send();
                    }),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListNewsletterSubscribers::route('/'),
        ];
    }
}
