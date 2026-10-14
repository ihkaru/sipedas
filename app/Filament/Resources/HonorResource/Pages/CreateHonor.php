<?php

namespace App\Filament\Resources\HonorResource\Pages;

use App\Filament\Resources\HonorResource;
use App\Models\Honor;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Str;

class CreateHonor extends CreateRecord
{
    protected static string $resource = HonorResource::class;

    protected function beforeCreate(): void
    {
        $kegiatanId = $this->data['kegiatan_manmit_id'] ?? null;
        $jabatan = $this->data['jabatan'] ?? null;
        $jenisHonor = $this->data['jenis_honor'] ?? null;

        if ($kegiatanId && $jabatan && $jenisHonor) {
            $computedId = Str::upper(trim($kegiatanId) . '-' . trim($jabatan) . '-' . trim($jenisHonor));

            if (Honor::where('id', $computedId)->exists()) {
                Notification::make()
                    ->danger()
                    ->title('Honor Sudah Terdaftar')
                    ->body("Honor dengan ID '{$computedId}' sudah terdaftar di sistem. Gunakan kombinasi kegiatan, jabatan, atau jenis honor yang berbeda.")
                    ->persistent()
                    ->send();

                $this->halt();
            }
        }
    }
}
