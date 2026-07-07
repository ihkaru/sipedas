<?php

namespace App\Filament\Resources\RiwayatPpkResource\Pages;

use App\Filament\Resources\RiwayatPpkResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditRiwayatPpk extends EditRecord
{
    protected static string $resource = RiwayatPpkResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
