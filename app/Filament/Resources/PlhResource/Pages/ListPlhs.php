<?php

namespace App\Filament\Resources\PlhResource\Pages;

use App\Filament\Resources\PlhResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPlhs extends ListRecords
{
    protected static string $resource = PlhResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
