<?php

namespace App\Filament\Resources\Articles\Pages;

use App\Filament\Pages\DashboardArticles;
use App\Filament\Resources\Articles\ArticleResource;
use Filament\Resources\Pages\ListRecords;

class ListArticles extends ListRecords
{
    protected static string $resource = ArticleResource::class;

    public function mount(): void
    {
        $this->redirect(DashboardArticles::getUrl(), navigate: true);
    }
}
