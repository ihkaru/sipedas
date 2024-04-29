<?php

namespace App\Filament\Resources\RiwayatPengajuanResource\Pages;

use App\Filament\Resources\RiwayatPengajuanResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditRiwayatPengajuan extends EditRecord
{
    protected static string $resource = RiwayatPengajuanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
