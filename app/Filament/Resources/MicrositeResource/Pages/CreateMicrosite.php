<?php

namespace App\Filament\Resources\MicrositeResource\Pages;

use App\Filament\Resources\MicrositeResource;
use Filament\Resources\Pages\CreateRecord;

class CreateMicrosite extends CreateRecord
{
    protected static string $resource = MicrositeResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['user_id'] = auth()->id();

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
