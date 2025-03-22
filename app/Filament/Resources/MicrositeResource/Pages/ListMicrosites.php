<?php

namespace App\Filament\Resources\MicrositeResource\Pages;

use App\Filament\Resources\MicrositeResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListMicrosites extends ListRecords
{
    protected static string $resource = MicrositeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
