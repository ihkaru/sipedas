<?php

namespace App\Filament\Resources\NomorSuratResource\Pages;

use App\Filament\Resources\NomorSuratResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListNomorSurats extends ListRecords
{
    protected static string $resource = NomorSuratResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
