<?php

namespace App\Filament\Resources\AlokasiHonorResource\Pages;

use App\Filament\Resources\AlokasiHonorResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAlokasiHonor extends EditRecord
{
    protected static string $resource = AlokasiHonorResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
