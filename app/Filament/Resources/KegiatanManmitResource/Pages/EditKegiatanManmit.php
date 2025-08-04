<?php

namespace App\Filament\Resources\KegiatanManmitResource\Pages;

use App\Filament\Resources\KegiatanManmitResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditKegiatanManmit extends EditRecord
{
    protected static string $resource = KegiatanManmitResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
