<?php

namespace App\Filament\Resources\HonorResource\Pages;

use App\Filament\Resources\HonorResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditHonor extends EditRecord
{
    protected static string $resource = HonorResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
