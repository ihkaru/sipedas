<?php

namespace App\Filament\Resources\Sipancong\StatusPembayaranResource\Pages;

use App\Filament\Resources\Sipancong\StatusPembayaranResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListStatusPembayarans extends ListRecords
{
    protected static string $resource = StatusPembayaranResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
