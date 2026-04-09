<?php

namespace App\Filament\Resources\Articles\Pages;

use App\Filament\Resources\Articles\ArticleResource;
use App\Services\CoverImageService;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Icons\Heroicon;

class EditArticle extends EditRecord
{
    protected static string $resource = ArticleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('preview')
                ->label('Aperçu')
                ->icon(Heroicon::OutlinedEye)
                ->url(fn (): string => $this->record->status === 'published'
                    ? url('/articles/' . $this->record->slug)
                    : url('/admin-preview/articles/' . $this->record->slug))
                ->openUrlInNewTab(),
            Actions\Action::make('regenerateCover')
                ->label('Générer une autre photo')
                ->icon(Heroicon::OutlinedPhoto)
                ->color('gray')
                ->requiresConfirmation()
                ->modalHeading('Générer une nouvelle cover IA ?')
                ->modalDescription("L'image actuelle sera remplacée par une nouvelle génération à partir du titre et de l'extrait. Si le visuel est rejeté ou trop lent, l'action s'arrêtera rapidement au lieu de rester bloquée.")
                ->action(function (CoverImageService $coverImageService): void {
                    $coverUrl = $coverImageService->generate(
                        (string) $this->record->title,
                        (string) ($this->record->excerpt ?? ''),
                        $this->record->category_id,
                        [
                            'max_attempts' => 1,
                            'generation_timeout' => 35,
                            'vision_timeout' => 20,
                            'vision_check' => true,
                        ],
                    );

                    if ($coverUrl === null) {
                        Notification::make()
                            ->danger()
                            ->title('Génération impossible')
                            ->body("Aucune image valide n'a pu être générée rapidement pour cet article. Relance si besoin.")
                            ->send();

                        return;
                    }

                    $this->record->update(['cover_image_url' => $coverUrl]);
                    $this->refreshFormData(['cover_image_url']);

                    Notification::make()
                        ->success()
                        ->title('Nouvelle cover générée')
                        ->body("L'image de couverture a été remplacée.")
                        ->send();
                }),
            Actions\DeleteAction::make(),
        ];
    }
}
