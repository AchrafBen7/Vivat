<?php

namespace App\Filament\Pages;

use App\Filament\Resources\Articles\ArticleResource;
use App\Filament\Resources\EnrichedItems\EnrichedItemResource;
use App\Filament\Resources\NewsletterSubscribers\NewsletterSubscriberResource;
use App\Filament\Resources\Payments\PaymentResource;
use App\Filament\Resources\RssFeeds\RssFeedResource;
use App\Filament\Resources\RssItems\RssItemResource;
use App\Filament\Resources\Sources\SourceResource;
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
            Action::make('pipelineFeeds')
                ->label('Flux RSS')
                ->icon(Heroicon::OutlinedRss)
                ->color('gray')
                ->url(RssFeedResource::getUrl('index')),
            Action::make('pipelineRawItems')
                ->label('Items RSS')
                ->icon(Heroicon::OutlinedDocumentMagnifyingGlass)
                ->color('gray')
                ->url(RssItemResource::getUrl('index')),
            Action::make('pipelineItems')
                ->label('Items IA')
                ->icon(Heroicon::OutlinedCpuChip)
                ->color('gray')
                ->url(EnrichedItemResource::getUrl('index')),
            Action::make('pipelineSources')
                ->label('Sources IA')
                ->icon(Heroicon::OutlinedGlobeAlt)
                ->color('gray')
                ->url(SourceResource::getUrl('index')),
            Action::make('pipelineProposals')
                ->label('Propositions IA')
                ->icon(Heroicon::OutlinedLightBulb)
                ->color('gray')
                ->url(PipelineProposals::getUrl()),
            Action::make('pipelineCronJobs')
                ->label('Cron jobs')
                ->icon(Heroicon::OutlinedClock)
                ->color('gray')
                ->url(PipelineCronJobs::getUrl()),
        ];
    }

    public function getSubheading(): ?string
    {
        return 'Accede directement a l’edito, aux paiements, a la newsletter et maintenant au pipeline de scraping / generation IA.';
    }
}
