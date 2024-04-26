<?php

namespace App\Filament\Resources\PlhResource\Pages;

use App\Filament\Resources\PlhResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPlh extends EditRecord
{
    protected static string $resource = PlhResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
