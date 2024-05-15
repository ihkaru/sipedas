<?php

namespace App\Filament\Resources\NomorSuratResource\Pages;

use App\Filament\Resources\NomorSuratResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditNomorSurat extends EditRecord
{
    protected static string $resource = NomorSuratResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
