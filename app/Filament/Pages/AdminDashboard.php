<?php

namespace App\Filament\Pages;

use App\Filament\Resources\Articles\ArticleResource;
use App\Filament\Resources\EnrichedItems\EnrichedItemResource;
use App\Filament\Resources\Payments\PaymentResource;
use App\Filament\Resources\Submissions\SubmissionResource;
use Filament\Actions\Action;
use Filament\Pages\Dashboard;
use Filament\Support\Icons\Heroicon;

class AdminDashboard extends Dashboard
{
    protected static ?string $title = 'Tableau de bord';

    protected function getHeaderActions(): array
    {
        return [
            Action::make('pendingSubmissions')
                ->label('Soumissions')
                ->icon(Heroicon::OutlinedInboxStack)
                ->color('primary')
                ->url(SubmissionResource::getUrl('index')),
            Action::make('publishedArticles')
                ->label('Articles')
                ->icon(Heroicon::OutlinedNewspaper)
                ->color('gray')
                ->url(ArticleResource::getUrl('index')),
            Action::make('payments')
                ->label('Paiements')
                ->icon(Heroicon::OutlinedCreditCard)
                ->color('gray')
                ->url(PaymentResource::getUrl('index')),
            Action::make('pipelineItems')
                ->label('Pipeline IA')
                ->icon(Heroicon::OutlinedCpuChip)
                ->color('gray')
                ->url(EnrichedItemResource::getUrl('index')),
        ];
    }

    public function getSubheading(): ?string
    {
        return null;
    }
}
