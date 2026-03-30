<?php

namespace App\Filament\Pages;

use App\Filament\Resources\Articles\ArticleResource;
use App\Filament\Resources\NewsletterSubscribers\NewsletterSubscriberResource;
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
                ->label('Voir les soumissions')
                ->icon(Heroicon::OutlinedInboxStack)
                ->color('primary')
                ->url(SubmissionResource::getUrl('index')),
            Action::make('publishedArticles')
                ->label('Gérer les articles')
                ->icon(Heroicon::OutlinedNewspaper)
                ->color('gray')
                ->url(ArticleResource::getUrl('index')),
            Action::make('payments')
                ->label('Voir les paiements')
                ->icon(Heroicon::OutlinedCreditCard)
                ->color('gray')
                ->url(PaymentResource::getUrl('index')),
            Action::make('newsletterSubscribers')
                ->label('Voir la newsletter')
                ->icon(Heroicon::OutlinedEnvelope)
                ->color('gray')
                ->url(NewsletterSubscriberResource::getUrl('index')),
        ];
    }

    public function getSubheading(): ?string
    {
        return 'Accede directement aux soumissions en attente pour les approuver ou les rejeter.';
    }
}
