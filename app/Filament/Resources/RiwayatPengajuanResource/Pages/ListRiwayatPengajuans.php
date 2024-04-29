<?php

namespace App\Filament\Resources\RiwayatPengajuanResource\Pages;

use App\Filament\Resources\RiwayatPengajuanResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListRiwayatPengajuans extends ListRecords
{
    protected static string $resource = RiwayatPengajuanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
