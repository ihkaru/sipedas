<?php

namespace App\Filament\Resources\PenugasanResource\Pages;

use App\DTO\PenugasanCreation;
use App\Filament\Resources\PenugasanResource;
use App\Models\Kegiatan;
use App\Models\MasterSls;
use App\Models\Pegawai;
use App\Models\Penugasan;
use App\Supports\Constants;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Forms\Components\Builder;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;

class ListPenugasans extends ListRecords
{
    protected static string $resource = PenugasanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make("tambah_pengajuan")
                ->visible(false)
                ->closeModalByClickingAway(false)
                ->label("Pengajuan Surat Tugas")
                ->icon("fluentui-document-add-20-o")
                ->form(PenugasanResource::formPengajuan())
                ->action(function (array $data): void {
                    if (Penugasan::ajukan($data)) {
                        Notification::make()
                            ->title('Pengajuan berhasil dikirim')
                            ->success()
                            ->send();
                    }
                }),
        ];
    }


}
