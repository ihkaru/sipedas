<?php

namespace App\Filament\Resources\Sipancong\StatusPembayaranResource\Pages;

use App\Filament\Resources\Sipancong\StatusPembayaranResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditStatusPembayaran extends EditRecord
{
    protected static string $resource = StatusPembayaranResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
