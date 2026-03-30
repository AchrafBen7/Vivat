<?php

namespace App\Filament\Pages;

use App\Jobs\GenerateArticleJob;
use App\Services\ArticleSelectionService;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;

class PipelineProposals extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedLightBulb;

    protected static string|\UnitEnum|null $navigationGroup = 'Pipeline IA';

    protected static ?int $navigationSort = 6;

    protected static ?string $navigationLabel = 'Propositions IA';

    protected static ?string $title = 'Propositions IA';

    protected string $view = 'filament.pages.pipeline-proposals';

    /**
     * @var array<int, array<string, mixed>>
     */
    public array $proposals = [];

    public function mount(ArticleSelectionService $selector): void
    {
        $this->proposals = $selector->selectBestTopics(6);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('refresh')
                ->label('Rafraîchir')
                ->icon(Heroicon::OutlinedArrowPath)
                ->color('gray')
                ->action(function (ArticleSelectionService $selector): void {
                    $this->proposals = $selector->selectBestTopics(6);

                    Notification::make()
                        ->success()
                        ->title('Propositions actualisées')
                        ->body('Le classement des meilleurs sujets IA vient d’être recalculé.')
                        ->send();
                }),
        ];
    }

    public function generateProposal(int $index): void
    {
        $proposal = $this->proposals[$index] ?? null;

        if (! is_array($proposal) || empty($proposal['items'])) {
            Notification::make()
                ->danger()
                ->title('Proposition introuvable')
                ->body('Cette proposition n’est plus disponible.')
                ->send();

            return;
        }

        $itemIds = collect($proposal['items'])
            ->pluck('id')
            ->filter()
            ->values()
            ->all();

        if ($itemIds === []) {
            Notification::make()
                ->danger()
                ->title('Aucun item exploitable')
                ->body('La proposition ne contient aucun item enrichi valide.')
                ->send();

            return;
        }

        GenerateArticleJob::dispatch(
            $itemIds,
            $proposal['category']['id'] ?? null,
            null,
            $proposal['suggested_article_type'] ?? 'standard',
            isset($proposal['suggested_min_words']) ? (int) $proposal['suggested_min_words'] : null,
            isset($proposal['suggested_max_words']) ? (int) $proposal['suggested_max_words'] : null,
            $proposal['context_priority'] ?? null,
        );

        Notification::make()
            ->success()
            ->title('Génération lancée')
            ->body('La proposition a été envoyée à la queue generation pour créer un brouillon IA.')
            ->send();
    }
}
