<?php

namespace App\Filament\Resources\Sipancong\JenisDokumenResource\Pages;

use App\Filament\Resources\Sipancong\JenisDokumenResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListJenisDokumens extends ListRecords
{
    protected static string $resource = JenisDokumenResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
