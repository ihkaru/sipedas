<?php

namespace App\Filament\Resources\MicrositeResource\Pages;

use App\Filament\Resources\MicrositeResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditMicrosite extends EditRecord
{
    protected static string $resource = MicrositeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
