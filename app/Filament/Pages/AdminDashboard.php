<?php

namespace App\Filament\Pages;

use App\Filament\Resources\Articles\ArticleResource;
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
                ->url(DashboardSubmissions::getUrl()),
            Action::make('publishedArticles')
                ->label('Articles')
                ->icon(Heroicon::OutlinedNewspaper)
                ->color('gray')
                ->url(DashboardArticles::getUrl()),
            Action::make('payments')
                ->label('Paiements')
                ->icon(Heroicon::OutlinedCreditCard)
                ->color('gray')
                ->url(DashboardPayments::getUrl()),
            Action::make('newsletter')
                ->label('Newsletter')
                ->icon(Heroicon::OutlinedEnvelope)
                ->color('gray')
                ->url(DashboardNewsletter::getUrl()),
            Action::make('pipelineItems')
                ->label('Pipeline IA')
                ->icon(Heroicon::OutlinedCpuChip)
                ->color('gray')
                ->url(PipelineStep1::getUrl()),
        ];
    }

    public function getSubheading(): ?string
    {
        return null;
    }
}
