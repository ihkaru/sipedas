<?php

namespace App\Filament\Resources\Sipancong\PengajuanResource\Pages;

use App\Filament\Resources\Sipancong\PengajuanResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPengajuan extends EditRecord
{
    protected static string $resource = PengajuanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
