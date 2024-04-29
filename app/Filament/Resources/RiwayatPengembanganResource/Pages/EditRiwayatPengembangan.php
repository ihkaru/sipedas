<?php

namespace App\Filament\Resources\RiwayatPengembanganResource\Pages;

use App\Filament\Resources\RiwayatPengembanganResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditRiwayatPengembangan extends EditRecord
{
    protected static string $resource = RiwayatPengembanganResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
