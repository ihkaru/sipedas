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
            Actions\Action::make('alokasikan_honor')
                ->label('Alokasikan Honor')
                ->icon('heroicon-o-user-plus')
                ->color('primary')
                ->url(fn() => url('/a/kegiatan-manmit/' . $this->record->kegiatan_manmit_id . '/edit'))
                ->openUrlInNewTab(false),
            Actions\DeleteAction::make(),
        ];
    }
}
