<?php

namespace App\Filament\Resources\CustomPageResource\Pages;

use App\Filament\Resources\CustomPageResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCustomPage extends EditRecord
{
    protected static string $resource = CustomPageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('copy_link')
                ->label('Copy Link')
                ->icon('heroicon-o-clipboard-document')
                ->color('info')
                ->extraAttributes(fn ($record) => [
                    'onclick' => "
                        const link = '" . route('custom-page.show', $record->slug) . "';
                        if (navigator.clipboard) {
                            navigator.clipboard.writeText(link).then(() => {
                                 new FilamentNotification().title('Link copied').success().send();
                            });
                        } else {
                            const el = document.createElement('textarea');
                            el.value = link;
                            document.body.appendChild(el);
                            el.select();
                            document.execCommand('copy');
                            document.body.removeChild(el);
                            new FilamentNotification().title('Link copied (fallback)').success().send();
                        }
                    ",
                ]),
            Actions\Action::make('view_page')
                ->label('View Page')
                ->icon('heroicon-o-arrow-top-right-on-square')
                ->color('gray')
                ->url(fn ($record) => route('custom-page.show', $record->slug))
                ->openUrlInNewTab(),
            Actions\DeleteAction::make(),
        ];
    }
}
