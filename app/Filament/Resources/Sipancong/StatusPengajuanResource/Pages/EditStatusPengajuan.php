<?php

namespace App\Filament\Resources\Sipancong\StatusPengajuanResource\Pages;

use App\Filament\Resources\Sipancong\StatusPengajuanResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditStatusPengajuan extends EditRecord
{
    protected static string $resource = StatusPengajuanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
