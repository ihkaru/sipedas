<?php

namespace App\Filament\Resources\Sipancong\PosisiDokumenResource\Pages;

use App\Filament\Resources\Sipancong\PosisiDokumenResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPosisiDokumens extends ListRecords
{
    protected static string $resource = PosisiDokumenResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
